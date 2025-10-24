<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModelEnum, ReadBuffer, WriteBuffer};
use FBE\Types\Side;

/**
 * FieldModel for Side enum
 *
 * Underlying type: int32 (4 bytes)
 * Note: Standard and Final are identical for enums (always inline, fixed-size)
 */
final class FieldModelSide extends FieldModelEnum
{
    protected function underlyingSize(): int
    {
        return 4; // int32
    }

    protected function readValue(ReadBuffer $buffer, int $offset): int
    {
        return $buffer->readInt32($offset);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, int $value): void
    {
        $buffer->writeInt32($offset, $value);
    }

    protected function fromValue(int $value): \BackedEnum
    {
        return Side::from($value);
    }

    protected function toValue(\BackedEnum $enum): int
    {
        if (!($enum instanceof Side)) {
            throw new \InvalidArgumentException('Expected Side enum');
        }
        return $enum->value;
    }
}
