<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding write buffer
 * 
 * Based on original FBE Python implementation with exact API compatibility
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class WriteBuffer
{
    private string $buffer;
    private int $size;
    private int $offset;

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

    public function size(): int
    {
        return $this->size;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function attachNew(): void
    {
        $this->buffer = '';
        $this->size = 0;
        $this->offset = 0;
    }

    public function attachCapacity(int $capacity): void
    {
        $this->buffer = str_repeat("\0", $capacity);
        $this->size = 0;
        $this->offset = 0;
    }

    public function attachBuffer(string $buffer, int $offset = 0, ?int $size = null): void
    {
        if ($size === null) {
            $size = strlen($buffer);
        }

        assert($size > 0, 'Invalid size!');
        assert($offset <= $size, 'Invalid offset!');

        $this->buffer = $buffer;
        $this->size = $size;
        $this->offset = $offset;
    }

    public function allocate(int $size): int
    {
        assert($size >= 0, 'Invalid allocation size!');

        $offset = $this->size;
        $total = $this->size + $size;

        if ($total > strlen($this->buffer)) {
            $newCapacity = max($total, strlen($this->buffer) * 2);
            $this->buffer .= str_repeat("\0", $newCapacity - strlen($this->buffer));
        }

        $this->size = $total;
        return $offset;
    }

    public function remove(int $offset, int $size): void
    {
        assert($offset + $size <= strlen($this->buffer), 'Invalid offset & size!');

        $this->buffer = substr($this->buffer, 0, $offset) . substr($this->buffer, $offset + $size);
        $this->size -= $size;

        if ($this->offset >= $offset + $size) {
            $this->offset -= $size;
        } elseif ($this->offset >= $offset) {
            $this->offset -= ($this->offset - $offset);
            if ($this->offset > $this->size) {
                $this->offset = $this->size;
            }
        }
    }

    public function reserve(int $capacity): void
    {
        assert($capacity >= 0, 'Invalid reserve capacity!');

        if ($capacity > strlen($this->buffer)) {
            $newCapacity = max($capacity, strlen($this->buffer) * 2);
            $this->buffer .= str_repeat("\0", $newCapacity - strlen($this->buffer));
        }
    }

    public function resize(int $size): void
    {
        $this->reserve($size);
        $this->size = $size;
        if ($this->offset > $this->size) {
            $this->offset = $this->size;
        }
    }

    public function reset(): void
    {
        $this->size = 0;
        $this->offset = 0;
    }

    public function shift(int $offset): void
    {
        $this->offset += $offset;
    }

    public function unshift(int $offset): void
    {
        $this->offset -= $offset;
    }

    // Write primitive types - ensure buffer has space before writing
    private function ensureSpace(int $offset, int $size): void
    {
        $required = $this->offset + $offset + $size;
        if ($required > strlen($this->buffer)) {
            $newCapacity = max($required, strlen($this->buffer) * 2, 1024);
            $this->buffer .= str_repeat("\0", $newCapacity - strlen($this->buffer));
        }
        // Update size to track written data
        $this->size = max($this->size, $required);
    }

    public function writeBool(int $offset, bool $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = $value ? "\x01" : "\x00";
    }

    public function writeInt8(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    public function writeUInt8(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 1);
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    public function writeInt16(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 2);
        $packed = pack('s', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt16(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 2);
        $packed = pack('S', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeInt32(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('l', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt32(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('L', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeInt64(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('q', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt64(int $offset, int $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('Q', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeFloat(int $offset, float $value): void
    {
        $this->ensureSpace($offset, 4);
        $packed = pack('f', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeDouble(int $offset, float $value): void
    {
        $this->ensureSpace($offset, 8);
        $packed = pack('d', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeString(int $offset, string $value): void
    {
        $len = strlen($value);
        $this->ensureSpace($offset, 4 + $len);
        
        // Write length as int32
        $packed = pack('l', $len);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
        
        // Write string data
        for ($i = 0; $i < $len; $i++) {
            $this->buffer[$this->offset + $offset + 4 + $i] = $value[$i];
        }
    }

    public function writeTimestamp(int $offset, int $value): void
    {
        $this->writeUInt64($offset, $value);
    }

    public function writeUuid(int $offset, string $value): void
    {
        $this->ensureSpace($offset, 16);
        // UUID as 16 bytes
        for ($i = 0; $i < 16; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $value[$i];
        }
    }

    public function writeBytes(int $offset, string $value): void
    {
        $len = strlen($value);
        $this->ensureSpace($offset, 4 + $len);
        
        // Write length as int32
        $packed = pack('l', $len);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
        
        // Write binary data
        for ($i = 0; $i < $len; $i++) {
            $this->buffer[$this->offset + $offset + 4 + $i] = $value[$i];
        }
    }

    public function writeDecimal(int $offset, string $value, int $scale, bool $negative): void
    {
        $this->ensureSpace($offset, 16);
        
        // Write unscaled value to bytes 0-11 (96-bit little-endian)
        for ($i = 0; $i < 12; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $value[$i] ?? "\x00";
        }
        
        // Bytes 12-13 are unused (zero)
        $this->buffer[$this->offset + $offset + 12] = "\x00";
        $this->buffer[$this->offset + $offset + 13] = "\x00";
        
        // Byte 14 = scale
        $this->buffer[$this->offset + $offset + 14] = chr($scale & 0xFF);
        
        // Byte 15 = sign
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
}

