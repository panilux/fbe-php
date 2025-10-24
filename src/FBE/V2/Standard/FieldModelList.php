<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Base class for list field models (Standard format)
 *
 * List is conceptually a linked list but serialized as sequential elements:
 * Binary: [4-byte pointer] → [4-byte count][elements...]
 *
 * Note: Binary format identical to Vector<T> in FBE spec
 */
abstract class FieldModelList extends FieldModel
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
