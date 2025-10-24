<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for uint16 (2 bytes, unsigned 0-65535)
 * Standard format: inline (no pointer)
 */
final class FieldModelUInt16 extends FieldModel
{
    public function size(): int { return 2; }

    public function get(): int
    {
        return $this->buffer->readUInt16($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeUInt16($this->offset, $value);
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
