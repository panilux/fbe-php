<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding field model base class (PHP 8.4+)
 * 
 * Base class for all FBE field models, providing buffer access
 * and offset management with modern PHP 8.4 features.
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
abstract class FieldModel
{
    /**
     * Buffer reference
     */
    protected WriteBuffer|ReadBuffer $buffer;
    
    /**
     * Field offset in buffer
     */
    public private(set) int $offset {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException("Offset cannot be negative");
            }
            $this->offset = $value;
        }
    }

    public function __construct(WriteBuffer|ReadBuffer $buffer, int $offset = 0)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
    }

    /**
     * Get field size in bytes
     */
    abstract public function size(): int;

    /**
     * Get extra size (for dynamic types like strings, vectors)
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Shift offset forward
     */
    public function shift(int $offset): void
    {
        $this->offset += $offset;
    }

    /**
     * Shift offset backward
     */
    public function unshift(int $offset): void
    {
        $this->offset -= $offset;
    }

    /**
     * Verify field value
     */
    public function verify(): bool
    {
        return true;
    }
}

