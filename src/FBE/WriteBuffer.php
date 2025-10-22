<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding write buffer (PHP 8.4+)
 *
 * Modern implementation using property hooks, asymmetric visibility,
 * and other PHP 8.4 features while maintaining FBE binary compatibility.
 */
final class WriteBuffer
{
    private string $buffer;

    /**
     * Current size of valid data in buffer
     * Uses property hooks for automatic validation
     */
    public private(set) int $size {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException("Size cannot be negative");
            }
            $this->size = $value;
        }
    }

    /**
     * Current offset for writing
     */
    public private(set) int $offset {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException("Offset cannot be negative");
            }
            $this->offset = $value;
        }
    }

    public function __construct(int $capacity = 0)
    {
        $this->buffer = str_repeat("\0", $capacity);
        $this->size = 0;
        $this->offset = 0;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    public function data(): string
    {
        return substr($this->buffer, 0, $this->size);
    }

    public function capacity(): int
    {
        return strlen($this->buffer);
    }

    /**
     * Reserve space in buffer
     */
    public function reserve(int $capacity): void
    {
        if ($capacity <= $this->capacity()) {
            return;
        }

        $this->buffer .= str_repeat("\0", $capacity - $this->capacity());
    }

    /**
     * Reset buffer to initial state
     */
    public function reset(): void
    {
        $this->size = 0;
        $this->offset = 0;
    }

    /**
     * Allocate space and return offset
     */
    public function allocate(int $size): int
    {
        $result = $this->offset + $this->size;
        $this->size += $size;

        // Grow buffer if needed
        if ($result + $size > $this->capacity()) {
            $this->reserve(max($result + $size, $this->capacity() * 2));
        }

        return $result;
    }

    /**
     * Ensure space is available at offset (helper for write methods)
     */
    private function ensureSpace(int $offset, int $size): void
    {
        $required = $offset + $size;

        if ($required > $this->size) {
            $this->size = $required;
        }

        if ($required > $this->capacity()) {
            $this->reserve(max($required, $this->capacity() * 2));
        }
    }

    /**
     * Write byte value (1 byte, unsigned, alias for uint8)
     */
    public function writeByte(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    /**
     * Write char value (1 byte, unsigned)
     */
    public function writeChar(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    /**
     * Write wchar value (4 bytes, little-endian, unsigned)
     */
    public function writeWChar(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('V', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write bool value (1 byte: 0 or 1)
     */
    public function writeBool(int $offset, bool $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = $value ? "\x01" : "\x00";
    }

    /**
     * Write int8 value
     */
    public function writeInt8(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = pack('c', $value);
    }

    /**
     * Write uint8 value
     */
    public function writeUInt8(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = pack('C', $value);
    }

    /**
     * Write int16 value (little-endian)
     */
    public function writeInt16(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 2);
        $packed = pack('s', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write uint16 value (little-endian)
     */
    public function writeUInt16(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 2);
        $packed = pack('v', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write int32 value (little-endian)
     */
    public function writeInt32(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('l', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write uint32 value (little-endian)
     */
    public function writeUInt32(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('V', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write int64 value (little-endian)
     */
    public function writeInt64(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('q', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write uint64 value (little-endian)
     */
    public function writeUInt64(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('P', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write float value (little-endian)
     */
    public function writeFloat(int $offset, float $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('f', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write double value (little-endian)
     */
    public function writeDouble(int $offset, float $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('d', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    /**
     * Write string value (size-prefixed)
     * Format: 4-byte size + UTF-8 bytes
     */
    public function writeString(int $offset, string $value): void
    {
        $bytes = $value;
        $size = strlen($bytes);

        $this->ensureSpace($offset, 4 + $size);

        // Write size
        $this->writeUInt32($offset, $size);

        // Write bytes
        for ($i = 0; $i < $size; $i++) {
            $this->buffer[$this->offset + $offset + 4 + $i] = $bytes[$i];
        }
    }

    /**
     * Write timestamp value (uint64, nanoseconds since epoch)
     */
    public function writeTimestamp(int $offset, int $nanoseconds): void
    {
        $this->writeUInt64($offset, $nanoseconds);
    }

    /**
     * Write UUID value (16 bytes)
     */
    public function writeUuid(int $offset, string $uuid): void
    {
        $this->ensureSpace($offset, 16);

        // Parse UUID string to binary
        $hex = str_replace('-', '', $uuid);
        $binary = hex2bin($hex);

        if ($binary === false || strlen($binary) !== 16) {
            throw new \InvalidArgumentException("Invalid UUID format: {$uuid}");
        }

        for ($i = 0; $i < 16; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $binary[$i];
        }
    }

    /**
     * Write bytes value (size-prefixed binary data)
     */
    public function writeBytes(int $offset, string $data): void
    {
        $size = strlen($data);

        $this->ensureSpace($offset, 4 + $size);

        // Write size
        $this->writeUInt32($offset, $size);

        // Write data
        for ($i = 0; $i < $size; $i++) {
            $this->buffer[$this->offset + $offset + 4 + $i] = $data[$i];
        }
    }

    /**
     * Write decimal value (.NET Decimal format, 16 bytes)
     */
    public function writeDecimal(int $offset, int $value, int $scale, bool $negative): void
    {
        $this->ensureSpace($offset, 16);

        // Bytes 0-11: Unscaled value (96-bit, little-endian)
        $low = $value & 0xFFFFFFFF;
        $mid = ($value >> 32) & 0xFFFFFFFF;
        $high = ($value >> 64) & 0xFFFFFFFF;

        $this->writeUInt32($offset, $low);
        $this->writeUInt32($offset + 4, $mid);
        $this->writeUInt32($offset + 8, $high);

        // Bytes 12-13: Unused (0)
        $this->buffer[$this->offset + $offset + 12] = "\x00";
        $this->buffer[$this->offset + $offset + 13] = "\x00";

        // Byte 14: Scale (0-28)
        $this->buffer[$this->offset + $offset + 14] = pack('C', $scale);

        // Byte 15: Sign
        $this->buffer[$this->offset + $offset + 15] = $negative ? "\x80" : "\x00";
    }

    /**
     * Write vector of int32 values
     * Format: 4-byte offset pointer → (4-byte size + elements)
     */
    public function writeVectorInt32(int $offset, array $values): int
    {
        // First ensure space for pointer
        $this->ensureSpace($offset, 4);

        $size = count($values);
        $dataSize = 4 + ($size * 4); // 4 bytes size + elements
        $dataOffset = $this->allocate($dataSize);

        // Write pointer at offset (relative to current offset)
        $this->writeUInt32($offset, $dataOffset - $this->offset);

        // Write size at data_offset
        $this->writeUInt32($dataOffset - $this->offset, $size);

        // Write elements
        foreach ($values as $i => $value) {
            $this->writeInt32($dataOffset - $this->offset + 4 + ($i * 4), $value);
        }

        return $dataSize;
    }

    /**
     * Write fixed-size array of int32 values (inline, no pointer)
     * Format: N × 4 bytes (elements only)
     */
    public function writeArrayInt32(int $offset, array $values): void
    {
        foreach ($values as $i => $value) {
            $this->writeInt32($offset + ($i * 4), $value);
        }
    }

    /**
     * Write map of int32 key-value pairs
     * Format: 4-byte offset pointer → (4-byte size + key-value pairs)
     * @param array $entries Associative array of key => value pairs
     */
    public function writeMapInt32(int $offset, array $entries): int
    {
        // First ensure space for pointer
        $this->ensureSpace($offset, 4);

        $size = count($entries);
        $dataSize = 4 + ($size * 8); // 4 bytes size + (key+value) pairs
        $dataOffset = $this->allocate($dataSize);

        // Write pointer at offset
        $this->writeUInt32($offset, $dataOffset - $this->offset);

        // Write size at data_offset
        $this->writeUInt32($dataOffset - $this->offset, $size);

        // Write key-value pairs
        $i = 0;
        foreach ($entries as $key => $value) {
            $this->writeInt32($dataOffset - $this->offset + 4 + ($i * 8), $key);
            $this->writeInt32($dataOffset - $this->offset + 4 + ($i * 8) + 4, $value);
            $i++;
        }

        return $dataSize;
    }

    /**
     * Write set of int32 values (unique values, same format as vector)
     * Format: 4-byte offset pointer → (4-byte size + elements)
     * Note: Uniqueness constraint enforced at application level
     */
    public function writeSetInt32(int $offset, array $values): int
    {
        return $this->writeVectorInt32($offset, $values);
    }

    // ========================================================================
    // Collections (String)
    // ========================================================================

    /**
     * Write vector of string values
     * Format: 4-byte offset pointer → (4-byte size + string elements)
     */
    public function writeVectorString(int $offset, array $values): int
    {
        $this->ensureSpace($offset, 4);

        $size = count($values);
        $dataSize = 4; // size prefix
        foreach ($values as $str) {
            $dataSize += 4 + strlen($str);
        }

        $dataOffset = $this->allocate($dataSize);
        $this->writeUInt32($offset, $dataOffset - $this->offset);
        $this->writeUInt32($dataOffset - $this->offset, $size);

        $currentOffset = $dataOffset - $this->offset + 4;
        foreach ($values as $str) {
            $this->writeString($currentOffset, $str);
            $currentOffset += 4 + strlen($str);
        }

        return $dataSize;
    }

    /**
     * Write fixed-size array of strings
     */
    public function writeArrayString(int $offset, array $values): int
    {
        $currentOffset = $offset;
        foreach ($values as $str) {
            $this->writeString($currentOffset, $str);
            $currentOffset += 4 + strlen($str);
        }
        return $currentOffset - $offset;
    }

    // ========================================================================
    // Collections (Float/Double)
    // ========================================================================

    public function writeVectorFloat(int $offset, array $values): int
    {
        $this->ensureSpace($offset, 4);
        $size = count($values);
        $dataSize = 4 + ($size * 4);
        $dataOffset = $this->allocate($dataSize);
        $this->writeUInt32($offset, $dataOffset - $this->offset);
        $this->writeUInt32($dataOffset - $this->offset, $size);
        foreach ($values as $i => $value) {
            $this->writeFloat($dataOffset - $this->offset + 4 + ($i * 4), $value);
        }
        return $dataSize;
    }

    public function writeArrayFloat(int $offset, array $values): int
    {
        foreach ($values as $i => $value) {
            $this->writeFloat($offset + ($i * 4), $value);
        }
        return count($values) * 4;
    }

    public function writeVectorDouble(int $offset, array $values): int
    {
        $this->ensureSpace($offset, 4);
        $size = count($values);
        $dataSize = 4 + ($size * 8);
        $dataOffset = $this->allocate($dataSize);
        $this->writeUInt32($offset, $dataOffset - $this->offset);
        $this->writeUInt32($dataOffset - $this->offset, $size);
        foreach ($values as $i => $value) {
            $this->writeDouble($dataOffset - $this->offset + 4 + ($i * 8), $value);
        }
        return $dataSize;
    }

    public function writeArrayDouble(int $offset, array $values): int
    {
        foreach ($values as $i => $value) {
            $this->writeDouble($offset + ($i * 8), $value);
        }
        return count($values) * 8;
    }


    // Optional types
    public function writeOptionalInt32(int $offset, ?int $value): void
    {
        if ($value === null) {
            $this->writeUInt8($offset, 0);  // has_value = false
            return;
        }

        $this->writeUInt8($offset, 1);  // has_value = true
        $dataOffset = $this->size;
        $this->writeUInt32($offset + 1, $dataOffset);  // pointer to data
        $this->writeInt32($dataOffset, $value);  // actual value
    }

    public function writeOptionalString(int $offset, ?string $value): void
    {
        if ($value === null) {
            $this->writeUInt8($offset, 0);  // has_value = false
            return;
        }

        $this->writeUInt8($offset, 1);  // has_value = true
        $dataOffset = $this->size;
        $this->writeUInt32($offset + 1, $dataOffset);  // pointer to data
        $this->writeString($dataOffset, $value);  // actual value
    }

    public function writeOptionalDouble(int $offset, ?float $value): void
    {
        if ($value === null) {
            $this->writeUInt8($offset, 0);  // has_value = false
            return;
        }

        $this->writeUInt8($offset, 1);  // has_value = true
        $dataOffset = $this->size;
        $this->writeUInt32($offset + 1, $dataOffset);  // pointer to data
        $this->writeDouble($dataOffset, $value);  // actual value
    }
}
