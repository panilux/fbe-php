<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for int8 (1 byte, signed -128 to 127)
 * Final format: inline (same as Standard for primitives)
 */
final class FieldModelInt8 extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): int
    {
        return $this->buffer->readInt8($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeInt8($this->offset, $value);
    }

    public function toJson(): int
    {
        return $this->get();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Expected int, got ' . get_debug_type($value));
        }
        $this->set($value);
    }
}
