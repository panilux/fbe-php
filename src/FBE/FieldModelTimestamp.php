<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding timestamp field model (pointer-based)
 */
final class FieldModelTimestamp extends FieldModel
{
    public function size(): int
    {
        return 4; // Pointer size
    }

    public function extra(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return 0;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        // Read size at pointer location
        $size = $this->buffer->readUInt32($pointer);
        return 4 + $size; // 4-byte size + data
    }

    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        return $this->buffer->readTimestamp($pointer);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $dataSize = $this->buffer->writeTimestamp($this->buffer->size(), $value);
        $dataOffset = $this->buffer->size() - $dataSize;

        // Write pointer
        $this->buffer->writeUInt32($this->offset, $dataOffset - $this->buffer->offset);
    }
}
