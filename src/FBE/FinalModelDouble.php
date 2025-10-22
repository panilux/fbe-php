<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding double final model (inline, compact)
 */
final class FinalModelDouble extends FinalModel
{
    public function size(): int
    {
        return 8;
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

        return $this->buffer->readDouble($this->offset);
    }

    public function set(float $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeDouble($this->offset, $value);
    }
}
