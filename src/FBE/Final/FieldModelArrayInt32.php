<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Fixed-size int32 array field model (Final format)
 *
 * Binary: N × 4 bytes (inline, no pointers)
 * Example: int32[10] = 40 bytes
 *
 * Note: Identical to Standard format for primitives
 */
final class FieldModelArrayInt32 extends FieldModelArray
{
    public function size(): int
    {
        return $this->arraySize * 4; // N × 4 bytes
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
