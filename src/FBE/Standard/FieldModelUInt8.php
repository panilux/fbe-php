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
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readUInt8($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeUInt8($this->offset, $value);
    }
}
