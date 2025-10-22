<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding wchar final model (inline, compact)
 */
final class FinalModelWChar extends FinalModel
{
    public function size(): int
    {
        return 4;
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

        return $this->buffer->readWChar($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeWChar($this->offset, $value);
    }
}
