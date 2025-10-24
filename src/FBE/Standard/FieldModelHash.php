<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Base class for hash (unordered map) field models (Standard format)
 *
 * Hash is an unordered associative container:
 * Binary: [4-byte pointer] → [4-byte count][key-value pairs...]
 *
 * Difference from Map:
 * - Hash: Unordered (insertion order or hash order)
 * - Map: Sorted by key
 *
 * Note: Faster insertion/lookup than Map (no sorting overhead)
 */
abstract class FieldModelHash extends FieldModel
{
    /**
     * Size in bytes (4-byte pointer)
     */
    public function size(): int
    {
        return 4;
    }

    /**
     * Extra data size (count + key-value pairs)
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
     * Get size of key in bytes
     * Returns -1 for variable-size keys (string)
     */
    abstract protected function keySize(): int;

    /**
     * Get size of value in bytes
     * Returns -1 for variable-size values (string)
     */
    abstract protected function valueSize(): int;

    /**
     * Read hash from buffer
     *
     * @return array Associative array (unordered)
     */
    abstract public function get(): array;

    /**
     * Write hash to buffer (preserves insertion order)
     *
     * @param array $value Associative array
     */
    abstract public function set(array $value): void;

    /**
     * Read key from buffer
     *
     * @return array ['value' => mixed, 'bytesRead' => int]
     */
    abstract protected function readKey(ReadBuffer $buffer, int $offset): array;

    /**
     * Read value from buffer
     *
     * @return array ['value' => mixed, 'bytesRead' => int]
     */
    abstract protected function readValue(ReadBuffer $buffer, int $offset): array;

    /**
     * Write key to buffer
     *
     * @return int Bytes written
     */
    abstract protected function writeKey(WriteBuffer $buffer, int $offset, mixed $key): int;

    /**
     * Write value to buffer
     *
     * @return int Bytes written
     */
    abstract protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int;
}
