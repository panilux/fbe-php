<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for uint8 (1 byte, unsigned 0-255)
 * Standard format: inline (no pointer)
 */
final class FieldModelUInt8 extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): int
    {
        return $this->buffer->readUInt8($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeUInt8($this->offset, $value);
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
