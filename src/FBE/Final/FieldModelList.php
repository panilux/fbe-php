<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Base class for list field models (Final format)
 *
 * List is conceptually a linked list but serialized inline:
 * Binary: [4-byte count][elements...]
 *
 * Note: Binary format identical to Vector<T> in FBE spec
 */
abstract class FieldModelList extends FieldModel
{
    /**
     * Size in bytes (minimum: 4-byte count)
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
     * Total size (all inline)
     */
    public function total(): int
    {
        return $this->size();
    }

    /**
     * Get size of single element in bytes
     * Returns -1 for variable-size elements (string, struct)
     */
    abstract protected function elementSize(): int;

    /**
     * Read list from buffer
     *
     * @return array List elements
     */
    abstract public function get(): array;

    /**
     * Write list to buffer
     *
     * @param array $value List elements
     */
    abstract public function set(array $value): void;
}
