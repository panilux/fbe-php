<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * FieldModel for char (1 byte, unsigned character 0-255)
 * Standard format: inline (no pointer)
 */
final class FieldModelChar extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): int
    {
        return $this->buffer->readChar($this->offset);
    }

    public function set(int $value): void
    {
        $this->buffer->writeChar($this->offset, $value);
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
