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

    // Write primitive types
    public function writeBool(int $offset, bool $value): void
    {
        $this->buffer[$this->offset + $offset] = chr($value ? 1 : 0);
    }

    public function writeInt8(int $offset, int $value): void
    {
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    public function writeUInt8(int $offset, int $value): void
    {
        $this->buffer[$this->offset + $offset] = chr($value & 0xFF);
    }

    public function writeInt16(int $offset, int $value): void
    {
        $packed = pack('s', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt16(int $offset, int $value): void
    {
        $packed = pack('S', $value);
        for ($i = 0; $i < 2; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeInt32(int $offset, int $value): void
    {
        $packed = pack('l', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt32(int $offset, int $value): void
    {
        $packed = pack('L', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeInt64(int $offset, int $value): void
    {
        $packed = pack('q', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeUInt64(int $offset, int $value): void
    {
        $packed = pack('Q', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeFloat(int $offset, float $value): void
    {
        $packed = pack('f', $value);
        for ($i = 0; $i < 4; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }

    public function writeDouble(int $offset, float $value): void
    {
        $packed = pack('d', $value);
        for ($i = 0; $i < 8; $i++) {
            $this->buffer[$this->offset + $offset + $i] = $packed[$i];
        }
    }
}

