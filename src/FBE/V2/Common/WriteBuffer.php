<?php

declare(strict_types=1);

namespace FBE\V2\Common;

use FBE\V2\Exceptions\BufferUnderflowException;

/**
 * Fast Binary Encoding write buffer with optimized operations
 *
 * PERFORMANCE: Uses bulk operations instead of character-by-character loops
 * All integers use little-endian encoding (FBE spec)
 */
final class WriteBuffer extends Buffer
{
    /**
     * Create new write buffer
     *
     * @param int $initialCapacity Initial buffer capacity
     */
    public function __construct(int $initialCapacity = 4096)
    {
        if ($initialCapacity < 0) {
            throw new BufferUnderflowException($initialCapacity, 'capacity');
        }

        $this->data = str_repeat("\0", $initialCapacity);
        $this->size = 0;
        $this->offset = 0;
    }

    /**
     * Get current buffer data (only valid bytes)
     */
    public function data(): string
    {
        return substr($this->data, 0, $this->size);
    }

    /**
     * Clear buffer and reset
     */
    public function clear(): void
    {
        $this->size = 0;
        $this->offset = 0;
    }

    /**
     * Reserve buffer capacity (grow if needed)
     */
    public function reserve(int $capacity): void
    {
        if ($capacity <= $this->capacity()) {
            return;
        }

        // Exponential growth: max(requested, 2x current)
        $newCapacity = max($capacity, $this->capacity() * 2);
        $this->data .= str_repeat("\0", $newCapacity - $this->capacity());
    }

    /**
     * Allocate space and return absolute offset
     *
     * @param int $size Number of bytes to allocate
     * @return int Absolute offset of allocated space
     */
    public function allocate(int $size): int
    {
        if ($size < 0) {
            throw new BufferUnderflowException($size, 'allocation size');
        }

        $result = $this->offset + $this->size;
        $this->size += $size;

        // Grow buffer if needed
        if ($result + $size > $this->capacity()) {
            $this->reserve($result + $size);
        }

        return $result;
    }

    /**
     * Ensure space is available at offset
     */
    private function ensureSpace(int $offset, int $size): void
    {
        if ($offset < 0) {
            throw new BufferUnderflowException($offset, 'offset');
        }

        if ($size < 0) {
            throw new BufferUnderflowException($size, 'size');
        }

        $required = $this->offset + $offset + $size;

        if ($required > $this->size) {
            $this->size = $required;
        }

        if ($required > $this->capacity()) {
            $this->reserve($required);
        }
    }

    /**
     * OPTIMIZED: Write raw bytes (bulk operation, not character-by-character!)
     */
    private function writeRawBytes(int $offset, string $bytes): void
    {
        $length = strlen($bytes);
        $this->ensureSpace($offset, $length);

        $absoluteOffset = $this->absoluteOffset($offset);

        // PERFORMANCE: Use substr_replace for bulk write (not loop!)
        $this->data = substr_replace($this->data, $bytes, $absoluteOffset, $length);
    }

    // ========================================================================
    // PRIMITIVE TYPES (Fixed-Size)
    // ========================================================================

    /**
     * Write bool (1 byte)
     */
    public function writeBool(int $offset, bool $value): void
    {
        $this->writeRawBytes($offset, $value ? "\x01" : "\x00");
    }

    /**
     * Write int8 (1 byte, signed)
     */
    public function writeInt8(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('c', $value));
    }

    /**
     * Write uint8 (1 byte, unsigned)
     */
    public function writeUInt8(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('C', $value));
    }

    /**
     * Write int16 (2 bytes, little-endian, signed)
     */
    public function writeInt16(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('s', $value));
    }

    /**
     * Write uint16 (2 bytes, little-endian, unsigned)
     */
    public function writeUInt16(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('v', $value));
    }

    /**
     * Write int32 (4 bytes, little-endian, signed)
     */
    public function writeInt32(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('l', $value));
    }

    /**
     * Write uint32 (4 bytes, little-endian, unsigned)
     */
    public function writeUInt32(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('V', $value));
    }

    /**
     * Write int64 (8 bytes, little-endian, signed)
     */
    public function writeInt64(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('q', $value));
    }

    /**
     * Write uint64 (8 bytes, little-endian, unsigned)
     */
    public function writeUInt64(int $offset, int $value): void
    {
        $this->writeRawBytes($offset, pack('P', $value));
    }

    /**
     * Write float (4 bytes, IEEE 754)
     */
    public function writeFloat(int $offset, float $value): void
    {
        $this->writeRawBytes($offset, pack('f', $value));
    }

    /**
     * Write double (8 bytes, IEEE 754)
     */
    public function writeDouble(int $offset, float $value): void
    {
        $this->writeRawBytes($offset, pack('d', $value));
    }

    // ========================================================================
    // STRING OPERATIONS (Standard vs Final formats)
    // ========================================================================

    /**
     * Write string INLINE (Final format: 4-byte size + UTF-8 data)
     *
     * @return int Bytes written (4 + string length)
     */
    public function writeStringInline(int $offset, string $value): int
    {
        $size = strlen($value);
        $this->writeUInt32($offset, $size);

        if ($size > 0) {
            $this->writeRawBytes($offset + 4, $value);
        }

        return 4 + $size;
    }

    /**
     * Write string POINTER (Standard format: 4-byte pointer → 4-byte size + UTF-8 data)
     *
     * @return int Pointer offset
     */
    public function writeStringPointer(int $offset, string $value): int
    {
        $size = strlen($value);

        // Allocate space for size + data
        $pointer = $this->allocate(4 + $size);

        // Write pointer at field offset
        $this->writeUInt32($offset, $pointer);

        // Write size and data at pointer location
        $this->writeUInt32($pointer, $size);

        if ($size > 0) {
            $this->writeRawBytes($pointer + 4, $value);
        }

        return $pointer;
    }

    /**
     * Write raw string data (no size prefix)
     * Helper for Map and other collection types
     *
     * @param int $offset Offset to write at
     * @param string $value String value
     * @param int $size Size to write (default: full string length)
     */
    public function writeString(int $offset, string $value, int $size = -1): void
    {
        if ($size < 0) {
            $size = strlen($value);
        }

        if ($size > 0) {
            $this->writeRawBytes($offset, substr($value, 0, $size));
        }
    }

    // ========================================================================
    // BYTES OPERATIONS
    // ========================================================================

    /**
     * Write bytes INLINE (Final format: 4-byte size + binary data)
     *
     * @return int Bytes written (4 + data length)
     */
    public function writeBytesInline(int $offset, string $data): int
    {
        $size = strlen($data);
        $this->writeUInt32($offset, $size);

        if ($size > 0) {
            $this->writeRawBytes($offset + 4, $data);
        }

        return 4 + $size;
    }

    /**
     * Write bytes POINTER (Standard format: 4-byte pointer → 4-byte size + binary data)
     *
     * @return int Pointer offset
     */
    public function writeBytesPointer(int $offset, string $data): int
    {
        $size = strlen($data);

        // Allocate space for size + data
        $pointer = $this->allocate(4 + $size);

        // Write pointer at field offset
        $this->writeUInt32($offset, $pointer);

        // Write size and data at pointer location
        $this->writeUInt32($pointer, $size);

        if ($size > 0) {
            $this->writeRawBytes($pointer + 4, $data);
        }

        return $pointer;
    }

    // ========================================================================
    // TIMESTAMP (8 bytes, nanoseconds since epoch)
    // ========================================================================

    /**
     * Write timestamp (uint64, nanoseconds since Unix epoch)
     */
    public function writeTimestamp(int $offset, int $nanoseconds): void
    {
        $this->writeUInt64($offset, $nanoseconds);
    }

    // ========================================================================
    // UUID (16 bytes, big-endian)
    // ========================================================================

    /**
     * Write UUID (16 bytes, RFC 4122 big-endian)
     *
     * @param int $offset Offset to write at
     * @param \FBE\V2\Types\Uuid $uuid UUID to write
     */
    public function writeUuid(int $offset, \FBE\V2\Types\Uuid $uuid): void
    {
        $this->writeRawBytes($offset, $uuid->toBytes());
    }

    // ========================================================================
    // DECIMAL (16 bytes, .NET format)
    // ========================================================================

    /**
     * Write decimal (16 bytes, .NET Decimal format with 96-bit precision)
     *
     * @param int $offset Offset to write at
     * @param \FBE\V2\Types\Decimal $decimal Decimal to write
     */
    public function writeDecimal(int $offset, \FBE\V2\Types\Decimal $decimal): void
    {
        $this->writeRawBytes($offset, $decimal->toBytes());
    }
}
