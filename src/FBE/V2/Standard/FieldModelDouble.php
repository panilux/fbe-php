<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelDouble extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): float
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readDouble($this->offset);
    }

    public function set(float $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeDouble($this->offset, $value);
    }
}
