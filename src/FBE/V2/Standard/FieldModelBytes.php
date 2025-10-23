<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Bytes (POINTER-BASED: 4-byte pointer → data)
 */
final class FieldModelBytes extends FieldModel
{
    private int $cachedExtra = 0;

    public function size(): int { return 4; } // Pointer only

    public function extra(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedExtra;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        // Size at pointer location
        $size = $this->buffer->readUInt32($pointer);
        return 4 + $size; // size header + data
    }

    public function get(): string
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readBytesPointer($this->offset);
    }

    public function set(string $data): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeBytesPointer($this->offset, $data);

        // Cache extra size: 4-byte size header + data
        $this->cachedExtra = 4 + strlen($data);
    }
}
