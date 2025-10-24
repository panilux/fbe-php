<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for wchar (4 bytes, unsigned Unicode character)
 * Standard format: inline (no pointer)
 */
final class FieldModelWChar extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        return $this->buffer->readWChar($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeWChar($this->offset, $value);
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
