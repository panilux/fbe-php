<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Fixed-size int32 array field model (Standard format)
 *
 * Binary: N × 4 bytes (inline, no pointers)
 * Example: int32[10] = 40 bytes
 */
final class FieldModelArrayInt32 extends FieldModelArray
{
    protected function elementSize(): int
    {
        return 4; // int32 = 4 bytes
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        $result = [];
        $offset = $this->offset;

        for ($i = 0; $i < $this->arraySize; $i++) {
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

        $this->validateArraySize($value);

        $offset = $this->offset;

        foreach ($value as $element) {
            $this->buffer->writeInt32($offset, $element);
            $offset += 4;
        }
    }
}
