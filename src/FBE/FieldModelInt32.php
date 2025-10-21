<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding int32 field model (PHP 8.4+)
 * 
 * Field model for 32-bit signed integers with modern PHP 8.4 features.
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class FieldModelInt32 extends FieldModel
{
    /**
     * Get field size (4 bytes for int32)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Get int32 value from buffer
     */
    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readInt32($this->offset);
        }
        
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    /**
     * Set int32 value in buffer
     */
    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeInt32($this->offset, $value);
            return;
        }
        
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

