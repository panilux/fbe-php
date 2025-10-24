<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * List<int32> field model (Standard format)
 *
 * Binary: [4-byte pointer] → [4-byte count][int32 elements...]
 * Example: list {1, 2, 3} = 4 (ptr) + 4 (count) + 12 (data) = 20 bytes
 */
final class FieldModelListInt32 extends FieldModelList
{
    private int $extraSize = 0;

    protected function elementSize(): int
    {
        return 4; // int32 = 4 bytes
    }

    public function extra(): int
    {
        return $this->extraSize;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        // Read pointer
        $pointer = $this->buffer->readUInt32($this->offset);

        if ($pointer === 0) {
            $this->extraSize = 0;
            return [];
        }

        // Read count at pointer location
        $count = $this->buffer->readUInt32($pointer);
        $this->extraSize = 4 + ($count * 4);

        if ($count === 0) {
            return [];
        }

        // Read elements
        $result = [];
        $offset = $pointer + 4;

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->buffer->readInt32($offset);
            $offset += 4;
        }

        return $result;
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $count = count($value);
        $this->extraSize = 4 + ($count * 4);

        // Allocate space for count + elements
        $pointer = $this->buffer->allocate($this->extraSize);

        // Write pointer at field offset
        $this->buffer->writeUInt32($this->offset, $pointer);

        // Write count
        $this->buffer->writeUInt32($pointer, $count);

        // Write elements
        $offset = $pointer + 4;
        foreach ($value as $element) {
            $this->buffer->writeInt32($offset, $element);
            $offset += 4;
        }
    }
}
