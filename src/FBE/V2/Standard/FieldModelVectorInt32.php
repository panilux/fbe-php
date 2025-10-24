<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

/**
 * Standard format Vector<Int32>
 */
final class FieldModelVectorInt32 extends FieldModelVector
{
    protected function elementSize(): int
    {
        return 4;
    }

    protected function readElement(ReadBuffer $buffer, int $offset): int
    {
        return $buffer->readInt32($offset);
    }

    protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): void
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Vector element must be int');
        }
        $buffer->writeInt32($offset, $value);
    }
}
