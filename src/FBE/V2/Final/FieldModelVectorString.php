<?php

declare(strict_types=1);

namespace FBE\V2\Final;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

/**
 * Final format Vector<String> (INLINE)
 *
 * Each string is inline: [4-byte size + data]
 */
final class FieldModelVectorString extends FieldModelVector
{
    protected function elementSize(): int
    {
        return -1; // Variable size
    }

    protected function readElement(ReadBuffer $buffer, int $offset): array
    {
        return $buffer->readStringInline($offset);
    }

    protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Vector element must be string');
        }
        return $buffer->writeStringInline($offset, $value);
    }
}
