<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{ReadBuffer, WriteBuffer};

/**
 * Standard format Optional<Int32>
 */
final class FieldModelOptionalInt32 extends FieldModelOptional
{
    protected function valueSize(): int
    {
        return 4;
    }

    protected function readValue(ReadBuffer $buffer, int $offset): int
    {
        return $buffer->readInt32($offset);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): void
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Optional value must be int');
        }
        $buffer->writeInt32($offset, $value);
    }
}
