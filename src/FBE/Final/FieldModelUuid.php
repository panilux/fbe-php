<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};
use FBE\Types\Uuid;

/**
 * Final format UUID (INLINE: 16 bytes) - Same as Standard
 */
final class FieldModelUuid extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): Uuid
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readUuid($this->offset);
    }

    public function set(Uuid $uuid): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeUuid($this->offset, $uuid);
    }
}
