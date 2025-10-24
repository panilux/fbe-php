<?php

declare(strict_types=1);

namespace FBE\Standard;

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
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readChar($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeChar($this->offset, $value);
    }
}
