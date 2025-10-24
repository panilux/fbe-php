<?php

declare(strict_types=1);

namespace FBE\V2\Standard;

use FBE\V2\Common\{FieldModelEnum, ReadBuffer, WriteBuffer};
use FBE\V2\Types\OrderStatus;

/**
 * FieldModel for OrderStatus enum
 *
 * Underlying type: int8 (1 byte) - more compact than int32
 * Note: Standard and Final are identical for enums (always inline, fixed-size)
 */
final class FieldModelOrderStatus extends FieldModelEnum
{
    protected function underlyingSize(): int
    {
        return 1; // int8
    }

    protected function readValue(ReadBuffer $buffer, int $offset): int
    {
        return $buffer->readInt8($offset);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, int $value): void
    {
        $buffer->writeInt8($offset, $value);
    }

    protected function fromValue(int $value): \BackedEnum
    {
        return OrderStatus::from($value);
    }

    protected function toValue(\BackedEnum $enum): int
    {
        if (!($enum instanceof OrderStatus)) {
            throw new \InvalidArgumentException('Expected OrderStatus enum');
        }
        return $enum->value;
    }
}
