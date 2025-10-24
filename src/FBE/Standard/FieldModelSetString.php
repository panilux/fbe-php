<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Set<string> field model (Standard format)
 *
 * Binary: [4-byte pointer] → [4-byte count][pointers...][string data...]
 * Example: set {"cat", "dog", "cat"} → {"cat", "dog"} (sorted, unique)
 */
final class FieldModelSetString extends FieldModelSet
{
    private int $extraSize = 0;

    protected function elementSize(): int
    {
        return -1; // Variable size
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
        $mainPointer = $this->buffer->readUInt32($this->offset);

        if ($mainPointer === 0) {
            $this->extraSize = 0;
            return [];
        }

        // Read count
        $count = $this->buffer->readUInt32($mainPointer);

        if ($count === 0) {
            $this->extraSize = 4;
            return [];
        }

        // Read string pointers and data
        $result = [];
        $offset = $mainPointer + 4;
        $totalDataSize = 0;

        for ($i = 0; $i < $count; $i++) {
            $strPointer = $this->buffer->readUInt32($offset);
            $offset += 4;

            if ($strPointer === 0) {
                $result[] = '';
            } else {
                $size = $this->buffer->readUInt32($strPointer);
                $result[] = $this->buffer->readString($strPointer + 4, $size);
                $totalDataSize += 4 + $size;
            }
        }

        $this->extraSize = 4 + ($count * 4) + $totalDataSize;

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
        $pointerSize = 4 + ($count * 4); // count + N pointers

        // Allocate for count + pointers
        $mainPointer = $this->buffer->allocate($pointerSize);

        // Write main pointer
        $this->buffer->writeUInt32($this->offset, $mainPointer);

        // Write count
        $this->buffer->writeUInt32($mainPointer, $count);

        // Write string pointers and data
        $offset = $mainPointer + 4;
        $totalDataSize = 0;

        foreach ($uniqueSorted as $str) {
            $strPointer = $this->buffer->writeStringPointer($offset, $str);
            $totalDataSize += 4 + strlen($str);
            $offset += 4;
        }

        $this->extraSize = $pointerSize + $totalDataSize;
    }
}
