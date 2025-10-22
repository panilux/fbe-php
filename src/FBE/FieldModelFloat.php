<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding float field model (inline)
 */
final class FieldModelFloat extends FieldModel
{
    public function size(): int
    {
        return 4;
    }

    public function extra(): int
    {
        return 0;
    }

    public function get(): float
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readFloat($this->offset);
    }

    public function set(float $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeFloat($this->offset, $value);
    }
}
