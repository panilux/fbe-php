<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelInt64 extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        return $this->buffer->readInt64($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeInt64($this->offset, $value);
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
