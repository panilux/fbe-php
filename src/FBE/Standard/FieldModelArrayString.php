<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Fixed-size string array field model (Standard format)
 *
 * Binary: N × 4 bytes (pointers) + string data in extra space
 * Example: string[5] = 5 pointers (20 bytes) + variable string data
 */
final class FieldModelArrayString extends FieldModelArray
{
    private int $extraSize = 0;

    protected function elementSize(): int
    {
        return 4; // Pointer = 4 bytes
    }

    public function extra(): int
    {
        return $this->extraSize;
    }

    public function get(): array
    {

        $result = [];
        $offset = $this->offset;

        for ($i = 0; $i < $this->arraySize; $i++) {
            // Read pointer
            $pointer = $this->buffer->readUInt32($offset);

            if ($pointer === 0) {
                $result[] = '';
            } else {
                // Read string at pointer location
                $size = $this->buffer->readUInt32($pointer);
                $result[] = $this->buffer->readString($pointer + 4, $size);
            }

            $offset += 4;
        }

        return $result;
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $this->validateArraySize($value);

        // Reserve space for pointer array first (N × 4 bytes)
        // This ensures allocate() returns offsets AFTER the pointer area
        $pointerAreaSize = $this->arraySize * 4;
        $currentSize = strlen($this->buffer->data());

        if ($currentSize < $this->offset + $pointerAreaSize) {
            // Allocate pointer area if not already allocated
            $needed = ($this->offset + $pointerAreaSize) - $currentSize;
            if ($needed > 0) {
                $this->buffer->allocate($needed);
            }
        }

        $offset = $this->offset;
        $this->extraSize = 0;

        foreach ($value as $element) {
            $pointer = $this->buffer->writeStringPointer($offset, $element);
            $this->extraSize += 4 + strlen($element); // size prefix + data
            $offset += 4;
        }
    }
}
