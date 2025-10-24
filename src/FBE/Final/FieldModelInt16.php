<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for int16 (2 bytes, signed -32768 to 32767)
 * Standard format: inline (no pointer)
 */
final class FieldModelInt16 extends FieldModel
{
    public function size(): int { return 2; }

    public function get(): int
    {
        return $this->buffer->readInt16($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeInt16($this->offset, $value);
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
