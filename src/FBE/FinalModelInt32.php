<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding int32 final model (FBE pattern - inline)
 */
final class FinalModelInt32 extends FieldModel
{
    /**
     * Get field size (4 bytes)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Get extra size (0 for inline format)
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Get int32 value from buffer
     */
    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readInt32($this->offset);
    }

    /**
     * Set int32 value in buffer
     */
    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeInt32($this->offset, $value);
    }
}

