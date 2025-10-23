<?php

declare(strict_types=1);

namespace FBE\V2\Common;

/**
 * Base class for all struct models
 *
 * Standard format: 4-byte size header + fields (pointer-based)
 * Final format: No header, fields inline
 */
abstract class StructModel
{
    protected ReadBuffer|WriteBuffer $buffer;
    protected int $offset;

    public function __construct(ReadBuffer|WriteBuffer $buffer, int $offset)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
    }

    /**
     * Get the struct size in bytes
     * - Standard format: 4 (header) + field sizes
     * - Final format: sum of field sizes
     */
    abstract public function size(): int;

    /**
     * Get extra data size (pointer targets, etc.)
     * Only used in Standard format for pointer-based fields
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Get total size (size + extra)
     */
    public function total(): int
    {
        return $this->size() + $this->extra();
    }

    /**
     * Verify the struct is valid and complete
     * Returns true if all required fields are present
     */
    abstract public function verify(): bool;

    /**
     * Get the buffer offset for this struct
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get the buffer instance
     */
    public function getBuffer(): ReadBuffer|WriteBuffer
    {
        return $this->buffer;
    }
}
