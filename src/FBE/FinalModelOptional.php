<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding optional<T> final model (FBE pattern - inline)
 *
 * Format:
 * - 1 byte: has_value flag (0 = null, 1 = has value)
 * - If has_value: followed by inline value data (type-dependent)
 *
 * FinalModel version uses inline serialization (no pointers).
 */
final class FinalModelOptional extends FieldModel
{
    private FieldModel $valueModel;
    private int $valueOffset;

    /**
     * @param ReadBuffer|WriteBuffer $buffer
     * @param int $offset
     * @param FieldModel $valueModel Model for the wrapped type
     */
    public function __construct($buffer, int $offset, FieldModel $valueModel)
    {
        parent::__construct($buffer, $offset);
        $this->valueModel = $valueModel;
        $this->valueOffset = $offset + 1; // After has_value flag
        $this->valueModel->setOffset($this->valueOffset);
    }

    /**
     * Get field size (1 byte flag + value size if present)
     */
    public function size(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $hasValue = $this->buffer->readByte($this->offset);
            if ($hasValue === 0) {
                return 1; // Only flag byte
            }
            
            // Update value model offset and get its size
            $this->valueModel->setOffset($this->valueOffset);
            return 1 + $this->valueModel->size();
        }

        // For WriteBuffer, return minimum size (flag only)
        return 1;
    }

    /**
     * Get extra size (0 for inline format)
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Check if optional has a value
     */
    public function hasValue(): bool
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        return $this->buffer->readByte($this->offset) !== 0;
    }

    /**
     * Get optional value from buffer (inline format)
     * 
     * @return mixed|null Returns null if no value, otherwise the value
     */
    public function get(): mixed
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $hasValue = $this->buffer->readByte($this->offset);
        if ($hasValue === 0) {
            return null;
        }

        // Update value model offset and get value (inline)
        $this->valueModel->setOffset($this->valueOffset);
        return $this->valueModel->get();
    }

    /**
     * Set optional value in buffer (inline format)
     * 
     * @param mixed|null $value Value to set, or null for no value
     */
    public function set(mixed $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        if ($value === null) {
            // Write has_value = 0 (no value)
            $this->buffer->writeByte($this->offset, 0);
        } else {
            // Write has_value = 1 (has value)
            $this->buffer->writeByte($this->offset, 1);
            
            // Write value inline using value model
            $this->valueModel->setOffset($this->valueOffset);
            $this->valueModel->set($value);
        }
    }

    /**
     * Set offset and update value model offset
     */
    public function setOffset(int $offset): void
    {
        parent::setOffset($offset);
        $this->valueOffset = $offset + 1;
        $this->valueModel->setOffset($this->valueOffset);
    }
}


