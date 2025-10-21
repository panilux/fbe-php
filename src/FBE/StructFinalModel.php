<?php
/**
 * FBE StructFinalModel - Compact serialization without header
 * Maximum performance, no versioning support
 */

declare(strict_types=1);

namespace FBE;

use FBE\WriteBuffer;
use FBE\ReadBuffer;

abstract class StructFinalModel
{
    protected WriteBuffer $buffer;

    public function __construct(?WriteBuffer $buffer = null)
    {
        $this->buffer = $buffer ?? new WriteBuffer();
    }

    public function getBuffer(): WriteBuffer
    {
        return $this->buffer;
    }

    /**
     * Serialize struct data directly (no header)
     */
    abstract protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int;

    /**
     * Deserialize struct data directly (no header)
     */
    abstract protected function deserializeStruct(ReadBuffer $buffer, int $offset);

    /**
     * Serialize struct without header
     * Format: [struct data]
     * Returns size written
     */
    public function serialize($value): int
    {
        return $this->serializeStruct($value, $this->buffer, 0);
    }

    /**
     * Deserialize struct from buffer without header
     * Returns [value, size_read]
     */
    public function deserialize()
    {
        $readBuffer = new ReadBuffer($this->buffer->data());
        $value = $this->deserializeStruct($readBuffer, 0);

        // Calculate size from buffer
        $size = strlen($this->buffer->data());

        return [$value, $size];
    }
}

