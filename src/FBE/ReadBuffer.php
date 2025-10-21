<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding read buffer
 */
final class ReadBuffer
{
    private string $buffer;
    private int $size;
    private int $offset;

    public function __construct(?string $buffer = null)
    {
        if ($buffer !== null) {
            $this->buffer = $buffer;
            $this->size = strlen($buffer);
            $this->offset = 0;
        } else {
            $this->buffer = '';
            $this->size = 0;
            $this->offset = 0;
        }
    }

    public function data(): string
    {
        return $this->buffer;
    }

    public function capacity(): int
    {
        return $this->size;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function offset(): int
    {
        return $this->offset;
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

    public function reset(): void
    {
        $this->buffer = '';
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

    // Read primitive types
    public function readBool(int $offset): bool
    {
        return ord($this->buffer[$this->offset + $offset]) !== 0;
    }

    public function readInt8(int $offset): int
    {
        $value = ord($this->buffer[$this->offset + $offset]);
        return $value > 127 ? $value - 256 : $value;
    }

    public function readUInt8(int $offset): int
    {
        return ord($this->buffer[$this->offset + $offset]);
    }

    public function readInt16(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 2);
        return unpack('s', $data)[1];
    }

    public function readUInt16(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 2);
        return unpack('S', $data)[1];
    }

    public function readInt32(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 4);
        return unpack('l', $data)[1];
    }

    public function readUInt32(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 4);
        return unpack('L', $data)[1];
    }

    public function readInt64(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 8);
        return unpack('q', $data)[1];
    }

    public function readUInt64(int $offset): int
    {
        $data = substr($this->buffer, $this->offset + $offset, 8);
        return unpack('Q', $data)[1];
    }

    public function readFloat(int $offset): float
    {
        $data = substr($this->buffer, $this->offset + $offset, 4);
        return unpack('f', $data)[1];
    }

    public function readDouble(int $offset): float
    {
        $data = substr($this->buffer, $this->offset + $offset, 8);
        return unpack('d', $data)[1];
    }

    public function readString(int $offset): string
    {
        $len = $this->readInt32($offset);
        return substr($this->buffer, $this->offset + $offset + 4, $len);
    }

    public function readTimestamp(int $offset): int
    {
        return $this->readUInt64($offset);
    }

    public function readUuid(int $offset): string
    {
        return substr($this->buffer, $this->offset + $offset, 16);
    }

    public function readBytes(int $offset): string
    {
        $len = $this->readInt32($offset);
        return substr($this->buffer, $this->offset + $offset + 4, $len);
    }

    /**
     * Read decimal value
     * Returns array with keys: 'value' (string, 12 bytes), 'scale' (int), 'negative' (bool)
     */
    public function readDecimal(int $offset): array
    {
        // Read 96-bit unscaled value from bytes 0-11
        $value = substr($this->buffer, $this->offset + $offset, 12);
        
        // Read scale from byte 14
        $scale = ord($this->buffer[$this->offset + $offset + 14]);
        
        // Read sign from byte 15
        $negative = (ord($this->buffer[$this->offset + $offset + 15]) & 0x80) !== 0;
        
        return [
            'value' => $value,
            'scale' => $scale,
            'negative' => $negative,
        ];
    }

    /**
     * Read vector of int32 values
     * Format: 4-byte offset pointer → (4-byte size + elements)
     */
    public function readVectorInt32(int $offset): array
    {
        // Read pointer
        $dataOffset = $this->readUInt32($offset);
        if ($dataOffset === 0) {
            return [];
        }
        
        // Read size
        $size = $this->readUInt32($dataOffset);
        
        // Read elements
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readInt32($dataOffset + 4 + ($i * 4));
        }
        
        return $result;
    }

    /**
     * Read fixed-size array of int32 values (inline, no pointer)
     * Format: N × 4 bytes (elements only)
     */
    public function readArrayInt32(int $offset, int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readInt32($offset + ($i * 4));
        }
        return $result;
    }

    /**
     * Read map of int32 key-value pairs
     * Format: 4-byte offset pointer → (4-byte size + key-value pairs)
     * @return array Associative array of key => value pairs
     */
    public function readMapInt32(int $offset): array
    {
        // Read pointer
        $dataOffset = $this->readUInt32($offset);
        if ($dataOffset === 0) {
            return [];
        }
        
        // Read size
        $size = $this->readUInt32($dataOffset);
        
        // Read key-value pairs
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $key = $this->readInt32($dataOffset + 4 + ($i * 8));
            $value = $this->readInt32($dataOffset + 4 + ($i * 8) + 4);
            $result[$key] = $value;
        }
        
        return $result;
    }

    /**
     * Read set of int32 values (same format as vector)
     * Format: 4-byte offset pointer → (4-byte size + elements)
     */
    public function readSetInt32(int $offset): array
    {
        return $this->readVectorInt32($offset);
    }
}

