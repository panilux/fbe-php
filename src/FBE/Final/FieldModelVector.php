<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format Vector (INLINE)
 *
 * Layout: [4-byte size][elements...]
 *
 * Example: Vector<Int32>
 * - Offset 0: 4-byte count
 * - Offset 4: [int32][int32][int32]...
 *
 * No pointers! More compact than Standard format.
 */
abstract class FieldModelVector extends FieldModel
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
     * Get the size of each element in bytes
     * For variable-size elements (strings), return -1
     */
    abstract protected function elementSize(): int;

    /**
     * Read element at offset and return [value, bytes_consumed]
     */
    abstract protected function readElement(ReadBuffer $buffer, int $offset): array;

    /**
     * Write element at offset and return bytes_consumed
     */
    abstract protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): int;

    /**
     * Get vector as PHP array
     */
    public function get(): array
    {

        $count = $this->buffer->readUInt32($this->offset);
        $result = [];
        $elementOffset = $this->offset + 4;
        $totalSize = 4;

        for ($i = 0; $i < $count; $i++) {
            [$value, $consumed] = $this->readElement($this->buffer, $elementOffset);
            $result[] = $value;
            $elementOffset += $consumed;
            $totalSize += $consumed;
        }

        $this->cachedSize = $totalSize;
        $this->cachedCount = $count;

        return $result;
    }

    /**
     * Set vector from PHP array
     */
    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $count = count($values);

        // Write count
        $this->buffer->writeUInt32($this->offset, $count);

        // Write elements
        $elementOffset = $this->offset + 4;
        $totalSize = 4;

        foreach ($values as $value) {
            $consumed = $this->writeElement($this->buffer, $elementOffset, $value);
            $elementOffset += $consumed;
            $totalSize += $consumed;
        }

        $this->cachedSize = $totalSize;
        $this->cachedCount = $count;
    }

    /**
     * Get vector count
     */
    public function count(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedCount;
        }

        return $this->buffer->readUInt32($this->offset);
    }

    /**
     * Convert to JSON-encodable array
     *
     * Note: This works for primitive types and strings.
     * For complex types (UUID, Decimal), override in subclass.
     */
    public function toJson(): array
    {
        return $this->get();
    }

    /**
     * Set vector from JSON-decoded array
     *
     * Note: This works for primitive types and strings.
     * For complex types (UUID, Decimal), override in subclass.
     *
     * @param mixed $value JSON-decoded value (array expected)
     */
    public function fromJson(mixed $value): void
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('Expected array, got ' . get_debug_type($value));
        }
        $this->set($value);
    }
}
