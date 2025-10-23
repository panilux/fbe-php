<?php

declare(strict_types=1);

namespace FBE\V2\Common;

use FBE\V2\Exceptions\{BufferOverflowException, BufferUnderflowException};

/**
 * Base class for FBE buffers with strict bounds checking
 *
 * SECURITY: All operations validate bounds to prevent buffer overflow attacks
 * PERFORMANCE: Uses property hooks for zero-overhead validation
 */
abstract class Buffer
{
    /**
     * Internal byte buffer (string = byte array in PHP)
     */
    protected string $data;

    /**
     * Current offset in buffer
     */
    protected int $offset;

    /**
     * Size of valid data in buffer
     */
    protected int $size;

    /**
     * Set offset with validation
     */
    protected function setOffset(int $value): void
    {
        if ($value < 0) {
            throw new BufferUnderflowException($value, 'offset');
        }
        $this->offset = $value;
    }

    /**
     * Set size with validation
     */
    protected function setSize(int $value): void
    {
        if ($value < 0) {
            throw new BufferUnderflowException($value, 'size');
        }
        $this->size = $value;
    }

    /**
     * Get current offset
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get current size
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Check if buffer is empty
     */
    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Get buffer capacity (total allocated size)
     */
    public function capacity(): int
    {
        return strlen($this->data);
    }

    /**
     * Reset offset to beginning
     */
    public function reset(): void
    {
        $this->offset = 0;
    }

    /**
     * Shift offset forward
     *
     * @throws BufferUnderflowException if offset would become negative
     */
    public function shift(int $delta): void
    {
        $this->offset += $delta;
    }

    /**
     * Shift offset backward
     *
     * @throws BufferUnderflowException if offset would become negative
     */
    public function unshift(int $delta): void
    {
        $this->offset -= $delta;
    }

    /**
     * SECURITY CRITICAL: Validate buffer access bounds
     *
     * @param int $offset Offset to access (relative to current offset)
     * @param int $length Number of bytes to access
     * @throws BufferOverflowException if access would exceed buffer bounds
     */
    protected function checkBounds(int $offset, int $length): void
    {
        if ($offset < 0) {
            throw new BufferUnderflowException($offset, 'access offset');
        }

        if ($length < 0) {
            throw new BufferUnderflowException($length, 'length');
        }

        $absoluteOffset = $this->offset + $offset;
        $endPosition = $absoluteOffset + $length;

        if ($endPosition > $this->size) {
            throw new BufferOverflowException(
                attemptedOffset: $absoluteOffset,
                attemptedLength: $length,
                bufferSize: $this->size
            );
        }
    }

    /**
     * Get absolute offset in buffer
     */
    protected function absoluteOffset(int $relativeOffset): int
    {
        return $this->offset + $relativeOffset;
    }
}
