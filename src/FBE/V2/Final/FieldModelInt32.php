<?php

declare(strict_types=1);

namespace FBE\V2\Final;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelInt32 extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readInt32($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeInt32($this->offset, $value);
    }
}
