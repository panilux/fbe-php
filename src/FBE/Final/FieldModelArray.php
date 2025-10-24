<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Base class for fixed-size array field models (Final format)
 *
 * Arrays have FIXED size known at compile time:
 * - Binary: N × element_size (no size prefix)
 * - Element serialization is inline (no pointers)
 *
 * Example: int32[10] = 40 bytes inline
 * Example: string[5] = 5 inline strings (variable size)
 */
abstract class FieldModelArray extends FieldModel
{
    protected int $arraySize;

    public function __construct(ReadBuffer|WriteBuffer $buffer, int $offset, int $arraySize)
    {
        parent::__construct($buffer, $offset);

        if ($arraySize < 0) {
            throw new \InvalidArgumentException("Array size must be non-negative, got $arraySize");
        }

        $this->arraySize = $arraySize;
    }

    /**
     * Get fixed array size
     */
    public function arraySize(): int
    {
        return $this->arraySize;
    }

    /**
     * Size of fixed array in bytes
     * For variable-size elements (strings), returns minimum size
     */
    abstract public function size(): int;

    /**
     * Extra data size (not used in Final format - all inline)
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Read array of values from buffer
     *
     * @return array Array of elements
     */
    abstract public function get(): array;

    /**
     * Write array of values to buffer
     *
     * @param array $value Array of elements (must match arraySize)
     * @throws \InvalidArgumentException if array size mismatch
     */
    abstract public function set(array $value): void;

    /**
     * Validate array size matches expected size
     */
    protected function validateArraySize(array $value): void
    {
        $count = count($value);
        if ($count !== $this->arraySize) {
            throw new \InvalidArgumentException(
                "Array size mismatch: expected {$this->arraySize}, got $count"
            );
        }
    }
}
