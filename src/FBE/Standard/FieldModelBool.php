<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelBool extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): bool
    {
        return $this->buffer->readBool($this->offset);
    }

    public function set(bool $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeBool($this->offset, $value);
    }

    public function toJson(): bool
    {
        return $this->get();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException('Expected bool, got ' . get_debug_type($value));
        }
        $this->set($value);
    }
}
