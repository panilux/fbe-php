<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding string field model (FBE pattern - pointer-based)
 * 
 * Format:
 * - 4 bytes at field offset: pointer to string data
 * - At pointer: 4 bytes size + string bytes
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class FieldModelString extends FieldModel
{
    /**
     * Get field size (4 bytes pointer)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Get extra size (4 bytes size + string length)
     */
    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer === 0) {
                return 0;
            }
            
            $size = $this->buffer->readUInt32($pointer);
            return 4 + $size;
        }
        
        return 0;
    }

    /**
     * Get string value from buffer
     */
    public function get(): string
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }
        
        // Read pointer
        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return "";
        }
        
        // Read size
        $size = $this->buffer->readUInt32($pointer);
        if ($size === 0) {
            return "";
        }
        
        // Read string data
        $data = substr($this->buffer->buffer, $pointer + 4, $size);
        return $data;
    }

    /**
     * Set string value in buffer
     */
    public function set(string $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }
        
        $size = strlen($value);
        
        // Calculate pointer (current buffer size)
        $pointer = $this->buffer->size;
        
        // Write pointer at field offset
        $this->buffer->writeUInt32($this->offset, $pointer);
        
        // Allocate space for size + data
        $this->buffer->allocate(4 + $size);
        
        // Write size
        $this->buffer->writeUInt32($pointer, $size);
        
        // Write string data directly
        if ($size > 0) {
            // Use substr_replace or direct memory write
            $offset = $pointer + 4;
            for ($i = 0; $i < $size; $i++) {
                $this->buffer->writeInt8($offset + $i, ord($value[$i]));
            }
        }
    }
}

