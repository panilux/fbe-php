<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Final format Vector<Int32> (INLINE)
 */
final class FieldModelVectorInt32 extends FieldModelVector
{
    protected function elementSize(): int
    {
        return 4;
    }

    protected function readElement(ReadBuffer $buffer, int $offset): array
    {
        $value = $buffer->readInt32($offset);
        return [$value, 4];
    }

    protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Vector element must be int');
        }
        $buffer->writeInt32($offset, $value);
        return 4;
    }
}
