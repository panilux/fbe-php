<?php

declare(strict_types=1);

namespace FBE\Common;

use FBE\Exceptions\{BufferOverflowException, BufferUnderflowException};

/**
 * Base class for FBE buffers with strict bounds checking
 *
 * SECURITY: All operations validate bounds to prevent buffer overflow attacks
 * PERFORMANCE: Uses property hooks for zero-overhead validation
 */
abstract class Buffer
{
    /**
     * Internal byte buffer (string = byte array in PHP)
     */
    protected string $data;

    /**
     * Current offset in buffer
     */
    protected int $offset;

    /**
     * Size of valid data in buffer
     */
    protected int $size;

    /**
     * Set offset with validation
     */
    protected function setOffset(int $value): void
    {
        if ($value < 0) {
            throw new BufferUnderflowException($value, 'offset');
        }
        $this->offset = $value;
    }

    /**
     * Set size with validation
     */
    protected function setSize(int $value): void
    {
        if ($value < 0) {
            throw new BufferUnderflowException($value, 'size');
        }
        $this->size = $value;
    }

    /**
     * Get current offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get current size
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Check if buffer is empty
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Get buffer capacity (total allocated size)
     */
    public function capacity(): int
    {
        return strlen($this->data);
    }

    /**
     * Reset offset to beginning
     */
    public function reset(): void
    {
        $this->offset = 0;
    }

    /**
     * Shift offset forward
     *
     * @throws BufferUnderflowException if offset would become negative
     */
    public function shift(int $delta): void
    {
        $this->offset += $delta;
    }

    /**
     * Shift offset backward
     *
     * @throws BufferUnderflowException if offset would become negative
     */
    public function unshift(int $delta): void
    {
        $this->offset -= $delta;
    }

    /**
     * SECURITY CRITICAL: Validate buffer access bounds
     *
     * @param int $offset Offset to access (relative to current offset)
     * @param int $length Number of bytes to access
     * @throws BufferOverflowException if access would exceed buffer bounds
     */
    protected function checkBounds(int $offset, int $length): void
    {
        if ($offset < 0) {
            throw new BufferUnderflowException($offset, 'access offset');
        }

        if ($length < 0) {
            throw new BufferUnderflowException($length, 'length');
        }

        $absoluteOffset = $this->offset + $offset;
        $endPosition = $absoluteOffset + $length;

        if ($endPosition > $this->size) {
            throw new BufferOverflowException(
                attemptedOffset: $absoluteOffset,
                attemptedLength: $length,
                bufferSize: $this->size
            );
        }
    }

    /**
     * Get absolute offset in buffer
     */
    protected function absoluteOffset(int $relativeOffset): int
    {
        return $this->offset + $relativeOffset;
    }

    /**
     * Get raw buffer data
     * Both ReadBuffer and WriteBuffer can expose data for reading
     */
    public function data(): string
    {
        return $this->data;
    }

    // ========================================================================
    // READ METHODS - Available for both ReadBuffer and WriteBuffer
    // ========================================================================
    // WriteBuffer needs to read its own data for serialization, debugging, etc.
    // These methods are moved from ReadBuffer to make them available everywhere

    /**
     * Read bool (1 byte)
     */
    public function readBool(int $offset): bool
    {
        $this->checkBounds($offset, 1);
        return $this->data[$this->absoluteOffset($offset)] !== "\x00";
    }

    /**
     * Read int8 (1 byte, signed)
     */
    public function readInt8(int $offset): int
    {
        $this->checkBounds($offset, 1);
        return unpack('c', $this->data[$this->absoluteOffset($offset)])[1];
    }

    /**
     * Read uint8 (1 byte, unsigned)
     */
    public function readUInt8(int $offset): int
    {
        $this->checkBounds($offset, 1);
        return unpack('C', $this->data[$this->absoluteOffset($offset)])[1];
    }

    /**
     * Read int16 (2 bytes, little-endian, signed)
     */
    public function readInt16(int $offset): int
    {
        $this->checkBounds($offset, 2);
        return unpack('s', substr($this->data, $this->absoluteOffset($offset), 2))[1];
    }

    /**
     * Read uint16 (2 bytes, little-endian, unsigned)
     */
    public function readUInt16(int $offset): int
    {
        $this->checkBounds($offset, 2);
        return unpack('v', substr($this->data, $this->absoluteOffset($offset), 2))[1];
    }

    /**
     * Read int32 (4 bytes, little-endian, signed)
     */
    public function readInt32(int $offset): int
    {
        $this->checkBounds($offset, 4);
        return unpack('l', substr($this->data, $this->absoluteOffset($offset), 4))[1];
    }

    /**
     * Read uint32 (4 bytes, little-endian, unsigned)
     */
    public function readUInt32(int $offset): int
    {
        $this->checkBounds($offset, 4);
        return unpack('V', substr($this->data, $this->absoluteOffset($offset), 4))[1];
    }

    /**
     * Read int64 (8 bytes, little-endian, signed)
     */
    public function readInt64(int $offset): int
    {
        $this->checkBounds($offset, 8);
        return unpack('q', substr($this->data, $this->absoluteOffset($offset), 8))[1];
    }

    /**
     * Read uint64 (8 bytes, little-endian, unsigned)
     */
    public function readUInt64(int $offset): int
    {
        $this->checkBounds($offset, 8);
        return unpack('P', substr($this->data, $this->absoluteOffset($offset), 8))[1];
    }

    /**
     * Read float (4 bytes, IEEE 754)
     */
    public function readFloat(int $offset): float
    {
        $this->checkBounds($offset, 4);
        return unpack('f', substr($this->data, $this->absoluteOffset($offset), 4))[1];
    }

    /**
     * Read double (8 bytes, IEEE 754)
     */
    public function readDouble(int $offset): float
    {
        $this->checkBounds($offset, 8);
        return unpack('d', substr($this->data, $this->absoluteOffset($offset), 8))[1];
    }

    /**
     * Read char (1 byte, unsigned character)
     */
    public function readChar(int $offset): int
    {
        return $this->readUInt8($offset);
    }

    /**
     * Read wchar (4 bytes, little-endian, unsigned Unicode character)
     */
    public function readWChar(int $offset): int
    {
        return $this->readUInt32($offset);
    }

    // ========================================================================
    // STRING OPERATIONS (Standard vs Final formats)
    // ========================================================================

    /**
     * Read string INLINE (Final format: 4-byte size + UTF-8 data)
     *
     * @return array{0: string, 1: int} [value, bytes_consumed]
     */
    public function readStringInline(int $offset): array
    {
        $this->checkBounds($offset, 4);
        $size = $this->readUInt32($offset);

        if ($size === 0) {
            return ['', 4];
        }

        $this->checkBounds($offset + 4, $size);
        $value = substr($this->data, $this->absoluteOffset($offset + 4), $size);

        return [$value, 4 + $size];
    }

    /**
     * Read string POINTER (Standard format: 4-byte pointer → 4-byte size + UTF-8 data)
     *
     * @return string String value
     */
    public function readStringPointer(int $offset): string
    {
        $this->checkBounds($offset, 4);
        $pointer = $this->readUInt32($offset);

        if ($pointer === 0) {
            return '';
        }

        // Temporarily set offset to pointer location
        $savedOffset = $this->offset;
        $this->offset = 0;

        try {
            $this->checkBounds($pointer, 4);
            $size = $this->readUInt32($pointer);

            if ($size === 0) {
                return '';
            }

            $this->checkBounds($pointer + 4, $size);
            return substr($this->data, $pointer + 4, $size);
        } finally {
            $this->offset = $savedOffset;
        }
    }

    /**
     * Read raw string data (no size prefix)
     */
    public function readString(int $offset, int $size): string
    {
        if ($size === 0) {
            return '';
        }

        $this->checkBounds($offset, $size);
        return substr($this->data, $this->absoluteOffset($offset), $size);
    }

    // ========================================================================
    // BYTES OPERATIONS
    // ========================================================================

    /**
     * Read bytes INLINE (Final format: 4-byte size + binary data)
     *
     * @return array{0: string, 1: int} [data, bytes_consumed]
     */
    public function readBytesInline(int $offset): array
    {
        $this->checkBounds($offset, 4);
        $size = $this->readUInt32($offset);

        if ($size === 0) {
            return ['', 4];
        }

        $this->checkBounds($offset + 4, $size);
        $data = substr($this->data, $this->absoluteOffset($offset + 4), $size);

        return [$data, 4 + $size];
    }

    /**
     * Read bytes POINTER (Standard format: 4-byte pointer → 4-byte size + binary data)
     *
     * @return string Binary data
     */
    public function readBytesPointer(int $offset): string
    {
        $this->checkBounds($offset, 4);
        $pointer = $this->readUInt32($offset);

        if ($pointer === 0) {
            return '';
        }

        // Temporarily set offset to pointer location
        $savedOffset = $this->offset;
        $this->offset = 0;

        try {
            $this->checkBounds($pointer, 4);
            $size = $this->readUInt32($pointer);

            if ($size === 0) {
                return '';
            }

            $this->checkBounds($pointer + 4, $size);
            return substr($this->data, $pointer + 4, $size);
        } finally {
            $this->offset = $savedOffset;
        }
    }

    // ========================================================================
    // TIMESTAMP (8 bytes, nanoseconds since epoch)
    // ========================================================================

    /**
     * Read timestamp (uint64, nanoseconds since Unix epoch)
     */
    public function readTimestamp(int $offset): int
    {
        return $this->readUInt64($offset);
    }

    // ========================================================================
    // UUID (16 bytes, big-endian)
    // ========================================================================

    /**
     * Read UUID (16 bytes, RFC 4122 big-endian)
     */
    public function readUuid(int $offset): \FBE\Types\Uuid
    {
        $this->checkBounds($offset, 16);
        $bytes = substr($this->data, $this->absoluteOffset($offset), 16);
        return \FBE\Types\Uuid::fromBytes($bytes);
    }

    // ========================================================================
    // DECIMAL (16 bytes, .NET format)
    // ========================================================================

    /**
     * Read decimal (16 bytes, .NET Decimal format with 96-bit precision)
     */
    public function readDecimal(int $offset): \FBE\Types\Decimal
    {
        $this->checkBounds($offset, 16);
        $bytes = substr($this->data, $this->absoluteOffset($offset), 16);
        return \FBE\Types\Decimal::fromBytes($bytes);
    }

    // ========================================================================
    // OPTIONAL TYPES (Standard format)
    // ========================================================================

    /**
     * Check if optional value is present
     */
    public function hasValue(int $offset): bool
    {
        $this->checkBounds($offset, 1);
        return $this->readUInt8($offset) !== 0;
    }
}
