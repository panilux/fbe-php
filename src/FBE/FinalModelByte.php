<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding byte final model (inline, compact)
 */
final class FinalModelByte extends FinalModel
{
    public function size(): int
    {
        return 1;
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

        return $this->buffer->readByte($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeByte($this->offset, $value);
    }
}
