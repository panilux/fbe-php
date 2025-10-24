<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelDouble extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): float
    {
        return $this->buffer->readDouble($this->offset);
    }

    public function set(float $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeDouble($this->offset, $value);
    }

    public function toJson(): float
    {
        return $this->get();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_float($value) && !is_int($value)) {
            throw new \InvalidArgumentException('Expected float or int, got ' . get_debug_type($value));
        }
        $this->set((float)$value);
    }
}
