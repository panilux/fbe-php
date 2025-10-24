<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Base class for set field models (Standard format)
 *
 * Set contains unique, sorted values:
 * Binary: [4-byte pointer] → [4-byte count][sorted unique elements...]
 *
 * Set ensures:
 * - No duplicate values
 * - Elements are sorted for binary search
 * - Consistent serialization order
 */
abstract class FieldModelSet extends FieldModel
{
    /**
     * Size in bytes (4-byte pointer)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Extra data size (count + elements)
     */
    abstract public function extra(): int;

    /**
     * Total size (pointer + extra data)
     */
    public function total(): int
    {
        return $this->size() + $this->extra();
    }

    /**
     * Get size of single element in bytes
     * Returns -1 for variable-size elements (string)
     */
    abstract protected function elementSize(): int;

    /**
     * Read set from buffer
     *
     * @return array Unique sorted elements
     */
    abstract public function get(): array;

    /**
     * Write set to buffer (automatically deduplicates and sorts)
     *
     * @param array $value Elements (will be deduplicated and sorted)
     */
    abstract public function set(array $value): void;

    /**
     * Deduplicate and sort values
     *
     * @param array $value Input values
     * @return array Unique sorted values
     */
    protected function normalizeSet(array $value): array
    {
        // Remove duplicates
        $unique = array_unique($value, SORT_REGULAR);

        // Sort
        sort($unique, SORT_REGULAR);

        // Re-index array (remove gaps from array_unique)
        return array_values($unique);
    }
}
