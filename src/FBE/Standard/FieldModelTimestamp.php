<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Timestamp (INLINE: 8 bytes)
 */
final class FieldModelTimestamp extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readTimestamp($this->offset);
    }

    public function set(int $nanoseconds): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeTimestamp($this->offset, $nanoseconds);
    }
}
