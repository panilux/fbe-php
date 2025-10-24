<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for uint32 (4 bytes, unsigned 0-4294967295)
 * Standard format: inline (no pointer)
 */
final class FieldModelUInt32 extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        return $this->buffer->readUInt32($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeUInt32($this->offset, $value);
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
