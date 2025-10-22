<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding bool final model (inline, compact)
 */
final class FinalModelBool extends FinalModel
{
    public function size(): int
    {
        return 1;
    }

    public function extra(): int
    {
        return 0;
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
