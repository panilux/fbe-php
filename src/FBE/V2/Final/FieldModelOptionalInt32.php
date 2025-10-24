<?php

declare(strict_types=1);

namespace FBE\V2\Final;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

/**
 * Final format Optional<Int32> (INLINE)
 */
final class FieldModelOptionalInt32 extends FieldModelOptional
{
    protected function valueSize(): int
    {
        return 4; // Fixed size
    }

    protected function readValue(ReadBuffer $buffer, int $offset): array
    {
        $value = $buffer->readInt32($offset);
        return [$value, 4];
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Optional value must be int');
        }
        $buffer->writeInt32($offset, $value);
        return 4;
    }
}
