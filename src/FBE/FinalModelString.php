<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding string final model (FBE pattern - inline)
 *
 * Format:
 * - 4 bytes size + string bytes (inline, no pointer)
 */
final class FinalModelString extends FieldModel
{
    /**
     * Get field size (4 bytes size + string length)
     */
    public function size(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $size = $this->buffer->readUInt32($this->offset);
            return 4 + $size;
        }

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
     * Get string value from buffer (inline format)
     */
    public function get(): string
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        // Read size
        $size = $this->buffer->readUInt32($this->offset);
        if ($size === 0) {
            return "";
        }

        // Read string data (inline, right after size)
        $data = substr($this->buffer->buffer, $this->buffer->offset + $this->offset + 4, $size);
        return $data;
    }

    /**
     * Set string value in buffer (inline format)
     */
    public function set(string $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $size = strlen($value);

        // Write size
        $this->buffer->writeUInt32($this->offset, $size);

        // Write string data directly (inline)
        if ($size > 0) {
            $offset = $this->offset + 4;
            for ($i = 0; $i < $size; $i++) {
                $this->buffer->writeInt8($offset + $i, ord($value[$i]));
            }
        }
    }
}

