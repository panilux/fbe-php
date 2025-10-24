<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format Map (INLINE)
 *
 * Layout: [4-byte count][key1][value1][key2][value2]...
 *
 * Example: Map<String, Int32>
 * - Offset 0: 4-byte count
 * - Offset 4: [string key][int32 value][string key][int32 value]...
 *
 * No pointers! More compact than Standard format.
 */
abstract class FieldModelMap extends FieldModel
{
    private int $cachedSize = 4; // Default: just count header
    private int $cachedCount = 0;

    public function size(): int
    {
        return $this->cachedSize;
    }

    public function extra(): int
    {
        return 0; // All inline in Final format
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
     */
    abstract protected function readKey(ReadBuffer $buffer, int $offset): array;

    /**
     * Read value at offset and return [value, bytes_consumed]
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

        $count = $this->buffer->readUInt32($this->offset);
        $result = [];
        $elementOffset = $this->offset + 4;
        $totalSize = 4;

        for ($i = 0; $i < $count; $i++) {
            // Read key
            [$key, $keyConsumed] = $this->readKey($this->buffer, $elementOffset);
            $elementOffset += $keyConsumed;
            $totalSize += $keyConsumed;

            // Read value
            [$value, $valueConsumed] = $this->readValue($this->buffer, $elementOffset);
            $elementOffset += $valueConsumed;
            $totalSize += $valueConsumed;

            $result[$key] = $value;
        }

        $this->cachedSize = $totalSize;
        $this->cachedCount = $count;

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

        // Write count
        $this->buffer->writeUInt32($this->offset, $count);

        // Write key-value pairs
        $elementOffset = $this->offset + 4;
        $totalSize = 4;

        foreach ($values as $key => $value) {
            $keyConsumed = $this->writeKey($this->buffer, $elementOffset, $key);
            $elementOffset += $keyConsumed;
            $totalSize += $keyConsumed;

            $valueConsumed = $this->writeValue($this->buffer, $elementOffset, $value);
            $elementOffset += $valueConsumed;
            $totalSize += $valueConsumed;
        }

        $this->cachedSize = $totalSize;
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

        return $this->buffer->readUInt32($this->offset);
    }
}
