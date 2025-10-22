<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding bool field model (inline, no pointer)
 */
final class FieldModelBool extends FieldModel
{
    public function size(): int
    {
        return 1; // 1 byte for bool
    }

    public function extra(): int
    {
        return 0; // Inline, no extra space
    }

    public function get(): bool
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readBool($this->offset);
    }

    public function set(bool $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeBool($this->offset, $value);
    }
}

