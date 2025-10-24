<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};
use FBE\Types\Uuid;

/**
 * Standard format UUID (INLINE: 16 bytes)
 */
final class FieldModelUuid extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): Uuid
    {
        return $this->buffer->readUuid($this->offset);
    }

    public function set(Uuid $uuid): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeUuid($this->offset, $uuid);
    }

    public function toJson(): string
    {
        return $this->get()->toString();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected UUID string, got ' . get_debug_type($value));
        }
        $this->set(new Uuid($value));
    }
}
