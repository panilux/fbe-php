<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding uint64 field model (inline)
 */
final class FieldModelUInt64 extends FieldModel
{
    public function size(): int
    {
        return 8;
    }

    public function extra(): int
    {
        return 0;
    }

    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readUInt64($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeUInt64($this->offset, $value);
    }
}
