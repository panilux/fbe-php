<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Vector (POINTER-BASED)
 *
 * Layout: [4-byte pointer] → [4-byte size][elements...]
 *
 * Example: Vector<Int32>
 * - Offset 0: 4-byte pointer to vector data
 * - Pointer location: [4-byte count][int32][int32][int32]...
 */
abstract class FieldModelVector extends FieldModel
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

        // Size header + elements
        $count = $this->buffer->readUInt32($pointer);
        return 4 + ($count * $this->elementSize());
    }

    /**
     * Get the size of each element in bytes
     */
    abstract protected function elementSize(): int;

    /**
     * Read element at index from the vector data
     */
    abstract protected function readElement(ReadBuffer $buffer, int $offset): mixed;

    /**
     * Write element at index to the vector data
     */
    abstract protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): void;

    /**
     * Get vector as PHP array
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
            $result[] = $this->readElement($this->buffer, $elementOffset);
            $elementOffset += $this->elementSize();
        }

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
        $vectorSize = 4 + ($count * $this->elementSize());

        // Allocate space for vector data
        $pointer = $this->buffer->allocate($vectorSize);

        // Write pointer
        $this->buffer->writeUInt32($this->offset, $pointer);

        // Write count
        $this->buffer->writeUInt32($pointer, $count);

        // Write elements
        $elementOffset = $pointer + 4;
        foreach ($values as $value) {
            $this->writeElement($this->buffer, $elementOffset, $value);
            $elementOffset += $this->elementSize();
        }

        // Cache extra size
        $this->cachedExtra = $vectorSize;
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

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        return $this->buffer->readUInt32($pointer);
    }
}
