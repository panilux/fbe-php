<?php

declare(strict_types=1);

namespace FBE\Common;

/**
 * Base class for all FBE field models
 *
 * Field models provide type-safe serialization/deserialization
 * with offset management and size calculation.
 */
abstract class FieldModel
{
    /**
     * Buffer reference (read or write)
     */
    protected ReadBuffer|WriteBuffer $buffer;

    /**
     * Field offset in buffer
     */
    protected int $offset;

    /**
     * Create field model
     *
     * @param ReadBuffer|WriteBuffer $buffer Buffer to operate on
     * @param int $offset Field offset in buffer
     */
    public function __construct(ReadBuffer|WriteBuffer $buffer, int $offset = 0)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
    }

    /**
     * Get fixed field size in bytes
     *
     * For fixed-size types: actual size
     * For variable-size types: pointer size (4 bytes) or inline size prefix
     *
     * @return int Field size in bytes
     */
    abstract public function size(): int;

    /**
     * Get extra/dynamic size in bytes (for variable-length types)
     *
     * For fixed-size types: 0
     * For variable-size types: actual data size beyond the fixed field
     *
     * @return int Extra size in bytes
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Get total size (field + extra)
     *
     * @return int Total size in bytes
     */
    public function total(): int
    {
        return $this->size() + $this->extra();
    }

    /**
     * Verify field value (optional validation)
     *
     * @return bool True if valid
     */
    public function verify(): bool
    {
        return true;
    }

    /**
     * Get current offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Set offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * Shift offset forward
     */
    public function shift(int $delta): void
    {
        $this->offset += $delta;
    }

    /**
     * Shift offset backward
     */
    public function unshift(int $delta): void
    {
        $this->offset -= $delta;
    }
}
