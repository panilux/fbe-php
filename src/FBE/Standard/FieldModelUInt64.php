<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for uint64 (8 bytes, unsigned 0-18446744073709551615)
 * Standard format: inline (no pointer)
 */
final class FieldModelUInt64 extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        return $this->buffer->readUInt64($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeUInt64($this->offset, $value);
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
