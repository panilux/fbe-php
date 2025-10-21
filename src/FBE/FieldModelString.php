<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding string field model (PHP 8.4+)
 * 
 * Field model for size-prefixed UTF-8 strings with modern PHP 8.4 features.
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class FieldModelString extends FieldModel
{
    /**
     * Get field size (4 bytes for size prefix)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Get extra size (actual string length)
     */
    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $size = $this->buffer->readUInt32($this->offset);
            return $size;
        }
        
        return 0;
    }

    /**
     * Get string value from buffer
     */
    public function get(): string
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readString($this->offset);
        }
        
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    /**
     * Set string value in buffer
     */
    public function set(string $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeString($this->offset, $value);
            return;
        }
        
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

