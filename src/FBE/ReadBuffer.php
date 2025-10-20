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

    public function __construct()
    {
        $this->buffer = '';
        $this->size = 0;
        $this->offset = 0;
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
}

