<?php

declare(strict_types=1);

namespace FBE\Final;

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
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readUInt64($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeUInt64($this->offset, $value);
    }
}
