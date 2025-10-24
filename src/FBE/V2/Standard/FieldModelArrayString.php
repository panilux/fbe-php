<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

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
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

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

        $offset = $this->offset;
        $this->extraSize = 0;

        foreach ($value as $element) {
            $pointer = $this->buffer->writeStringPointer($offset, $element);
            $this->extraSize += 4 + strlen($element); // size prefix + data
            $offset += 4;
        }
    }
}
