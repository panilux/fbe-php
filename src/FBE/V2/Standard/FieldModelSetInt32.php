<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

/**
 * Set<int32> field model (Standard format)
 *
 * Binary: [4-byte pointer] → [4-byte count][sorted unique int32 values...]
 * Example: set {3, 1, 2, 1} → {1, 2, 3} = 4 (ptr) + 4 (count) + 12 (data) = 20 bytes
 */
final class FieldModelSetInt32 extends FieldModelSet
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

        // Read sorted unique elements
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

        // Deduplicate and sort
        $uniqueSorted = $this->normalizeSet($value);

        $count = count($uniqueSorted);
        $this->extraSize = 4 + ($count * 4);

        // Allocate space for count + elements
        $pointer = $this->buffer->allocate($this->extraSize);

        // Write pointer at field offset
        $this->buffer->writeUInt32($this->offset, $pointer);

        // Write count
        $this->buffer->writeUInt32($pointer, $count);

        // Write sorted unique elements
        $offset = $pointer + 4;
        foreach ($uniqueSorted as $element) {
            $this->buffer->writeInt32($offset, $element);
            $offset += 4;
        }
    }
}
