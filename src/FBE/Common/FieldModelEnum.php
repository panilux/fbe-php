<?php

declare(strict_types=1);

namespace FBE\Common;

/**
 * Base class for enum field models
 *
 * Enums are always inline (fixed-size) - no difference between Standard and Final
 * The underlying type determines the serialization size (int8, int16, int32, etc.)
 *
 * Example usage:
 * ```php
 * enum Side: int {
 *     case Buy = 0;
 *     case Sell = 1;
 * }
 *
 * class FieldModelSide extends FieldModelEnum {
 *     protected function underlyingSize(): int { return 4; } // int32
 *
 *     protected function readValue(ReadBuffer $buffer, int $offset): int {
 *         return $buffer->readInt32($offset);
 *     }
 *
 *     protected function writeValue(WriteBuffer $buffer, int $offset, int $value): void {
 *         $buffer->writeInt32($offset, $value);
 *     }
 *
 *     protected function fromValue(int $value): Side {
 *         return Side::from($value);
 *     }
 *
 *     protected function toValue(mixed $enum): int {
 *         return $enum->value;
 *     }
 * }
 * ```
 */
abstract class FieldModelEnum extends FieldModel
{
    public function size(): int
    {
        return $this->underlyingSize();
    }

    public function extra(): int
    {
        return 0; // Enums are always fixed-size, no extra data
    }

    /**
     * Get the size of the underlying type
     * Examples: 1 for int8, 2 for int16, 4 for int32, 8 for int64
     */
    abstract protected function underlyingSize(): int;

    /**
     * Read the underlying value from buffer
     */
    abstract protected function readValue(ReadBuffer $buffer, int $offset): int;

    /**
     * Write the underlying value to buffer
     */
    abstract protected function writeValue(WriteBuffer $buffer, int $offset, int $value): void;

    /**
     * Convert underlying value to enum instance
     * Use Enum::from() or Enum::tryFrom()
     *
     * @param int $value Underlying integer value
     * @return \BackedEnum Enum instance
     */
    abstract protected function fromValue(int $value): \BackedEnum;

    /**
     * Convert enum instance to underlying value
     * Use $enum->value
     *
     * @param \BackedEnum $enum Enum instance
     * @return int Underlying integer value
     */
    abstract protected function toValue(\BackedEnum $enum): int;

    /**
     * Get enum value from buffer
     */
    public function get(): \BackedEnum
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }

        $value = $this->readValue($this->buffer, $this->offset);
        return $this->fromValue($value);
    }

    /**
     * Set enum value to buffer
     */
    public function set(\BackedEnum $enum): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $value = $this->toValue($enum);
        $this->writeValue($this->buffer, $this->offset, $value);
    }
}
