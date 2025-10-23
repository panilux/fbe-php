<?php

declare(strict_types=1);

namespace FBE\V2\Final;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format Bytes (INLINE: 4-byte size + data)
 */
final class FieldModelBytes extends FieldModel
{
    private int $cachedSize = 4; // Default: just size header

    public function size(): int
    {
        return $this->cachedSize;
    }

    public function extra(): int
    {
        return 0; // Already included in size()
    }

    public function get(): string
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }

        [$data, $consumed] = $this->buffer->readBytesInline($this->offset);
        $this->cachedSize = $consumed;

        return $data;
    }

    public function set(string $data): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $this->cachedSize = $this->buffer->writeBytesInline($this->offset, $data);
    }
}
