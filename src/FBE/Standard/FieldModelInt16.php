<?php

declare(strict_types=1);

namespace FBE\Standard;

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
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readInt16($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeInt16($this->offset, $value);
    }
}
