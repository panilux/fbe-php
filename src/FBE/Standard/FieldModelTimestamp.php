<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Timestamp (INLINE: 8 bytes)
 */
final class FieldModelTimestamp extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        return $this->buffer->readTimestamp($this->offset);
    }

    public function set(int $nanoseconds): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeTimestamp($this->offset, $nanoseconds);
    }

    public function toJson(): int
    {
        return $this->get();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException('Expected int or float (nanoseconds), got ' . get_debug_type($value));
        }
        // Convert float to int if needed (PHP converts large ints to float)
        $this->set((int)$value);
    }
}
