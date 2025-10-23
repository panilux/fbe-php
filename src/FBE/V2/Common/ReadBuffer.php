<?php

declare(strict_types=1);

namespace FBE\V2\Common;

/**
 * Fast Binary Encoding read buffer with strict bounds checking
 *
 * SECURITY: All read operations validate bounds before access
 * IMMUTABLE: Buffer data cannot be modified after construction
 * All integers use little-endian encoding (FBE spec)
 */
final class ReadBuffer extends Buffer
{
    /**
     * Create new read buffer from binary data
     *
     * @param string $data Binary data to read from
     * @param int $offset Starting offset (default: 0)
     * @param int $size Size of valid data (default: entire buffer)
     */
    public function __construct(string $data, int $offset = 0, int $size = 0)
    {
        $this->data = $data;
        $this->offset = $offset;
        $this->size = $size === 0 ? strlen($data) : $size;
    }

    // ========================================================================
    // PRIMITIVE TYPES (Fixed-Size)
    // ========================================================================

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
    // OPTIONAL TYPES (Standard format)
    // ========================================================================

    /**
     * Check if optional value is present
     *
     * @param int $offset Offset of optional field (1-byte has_value flag)
     * @return bool True if value is present
     */
    public function hasValue(int $offset): bool
    {
        $this->checkBounds($offset, 1);
        return $this->readUInt8($offset) !== 0;
    }
}
