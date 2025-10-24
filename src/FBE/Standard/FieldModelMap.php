<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Map (POINTER-BASED)
 *
 * Layout: [4-byte pointer] → [4-byte count][key1][value1][key2][value2]...
 *
 * Example: Map<String, Int32>
 * - Offset 0: 4-byte pointer to map data
 * - Pointer location: [4-byte count][string key][int32 value][string key][int32 value]...
 */
abstract class FieldModelMap extends FieldModel
{
    private int $cachedExtra = 0;
    private int $cachedCount = 0;

    public function size(): int
    {
        return 4; // Pointer only
    }

    public function extra(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedExtra;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        // Size header + key-value pairs
        $count = $this->buffer->readUInt32($pointer);

        // If both key and value are fixed-size
        if ($this->keySize() > 0 && $this->valueSize() > 0) {
            return 4 + ($count * ($this->keySize() + $this->valueSize()));
        }

        // For variable-size, calculate by reading
        $totalSize = 4; // Count header
        $elementOffset = $pointer + 4;

        for ($i = 0; $i < $count; $i++) {
            // Read key and get bytes consumed
            [$key, $keyConsumed] = $this->readKey($this->buffer, $elementOffset);
            $elementOffset += $keyConsumed;
            $totalSize += $keyConsumed;

            // Read value and get bytes consumed
            [$value, $valueConsumed] = $this->readValue($this->buffer, $elementOffset);
            $elementOffset += $valueConsumed;
            $totalSize += $valueConsumed;
        }

        return $totalSize;
    }

    /**
     * Get the size of each key in bytes
     * For variable-size keys (strings), return -1
     */
    abstract protected function keySize(): int;

    /**
     * Get the size of each value in bytes
     * For variable-size values (strings), return -1
     */
    abstract protected function valueSize(): int;

    /**
     * Read key at offset and return [key, bytes_consumed]
     * For fixed-size: bytes_consumed = keySize()
     * For variable-size: bytes_consumed depends on data
     */
    abstract protected function readKey(ReadBuffer $buffer, int $offset): array;

    /**
     * Read value at offset and return [value, bytes_consumed]
     * For fixed-size: bytes_consumed = valueSize()
     * For variable-size: bytes_consumed depends on data
     */
    abstract protected function readValue(ReadBuffer $buffer, int $offset): array;

    /**
     * Write key at offset and return bytes_consumed
     */
    abstract protected function writeKey(WriteBuffer $buffer, int $offset, mixed $key): int;

    /**
     * Write value at offset and return bytes_consumed
     */
    abstract protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int;

    /**
     * Get map as PHP associative array
     */
    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return [];
        }

        $count = $this->buffer->readUInt32($pointer);
        $result = [];
        $elementOffset = $pointer + 4;

        for ($i = 0; $i < $count; $i++) {
            // Read key and get bytes consumed
            [$key, $keyConsumed] = $this->readKey($this->buffer, $elementOffset);
            $elementOffset += $keyConsumed;

            // Read value and get bytes consumed
            [$value, $valueConsumed] = $this->readValue($this->buffer, $elementOffset);
            $elementOffset += $valueConsumed;

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Set map from PHP associative array
     */
    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $count = count($values);

        // Calculate total size needed
        $pairSize = 0;
        if ($this->keySize() > 0 && $this->valueSize() > 0) {
            // Fixed-size keys and values
            $pairSize = ($this->keySize() + $this->valueSize()) * $count;
        } else {
            // Variable-size - calculate by simulating writes
            foreach ($values as $key => $value) {
                // For string keys
                if ($this->keySize() < 0) {
                    $pairSize += 4 + strlen((string)$key);
                } else {
                    $pairSize += $this->keySize();
                }

                // For string values
                if ($this->valueSize() < 0) {
                    $pairSize += 4 + strlen((string)$value);
                } else {
                    $pairSize += $this->valueSize();
                }
            }
        }

        $mapSize = 4 + $pairSize; // count header + pairs

        // Allocate space for map data
        $pointer = $this->buffer->allocate($mapSize);

        // Write pointer
        $this->buffer->writeUInt32($this->offset, $pointer);

        // Write count
        $this->buffer->writeUInt32($pointer, $count);

        // Write key-value pairs
        $elementOffset = $pointer + 4;
        $totalSize = 4;

        foreach ($values as $key => $value) {
            $keyConsumed = $this->writeKey($this->buffer, $elementOffset, $key);
            $elementOffset += $keyConsumed;
            $totalSize += $keyConsumed;

            $valueConsumed = $this->writeValue($this->buffer, $elementOffset, $value);
            $elementOffset += $valueConsumed;
            $totalSize += $valueConsumed;
        }

        // Cache extra size
        $this->cachedExtra = $totalSize;
        $this->cachedCount = $count;
    }

    /**
     * Get map entry count
     */
    public function count(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedCount;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        return $this->buffer->readUInt32($pointer);
    }
}
