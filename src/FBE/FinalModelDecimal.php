<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding decimal final model (inline, compact)
 */
final class FinalModelDecimal extends FinalModel
{
    public function size(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return 4; // Size field only
        }

        $size = $this->buffer->readUInt32($this->offset);
        return 4 + $size; // Size field + data
    }

    public function extra(): int
    {
        return 0;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readDecimal($this->offset);
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeDecimal($this->offset, $value);
    }
}
