<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Final format Map<String, Int32>
 *
 * Layout: [4-byte count][key1][value1][key2][value2]...
 * Key: [4-byte size][UTF-8 data]
 * Value: 4-byte int32
 *
 * Inline format - more compact than Standard (no pointers)
 */
final class FieldModelMapStringInt32 extends FieldModelMap
{
    protected function keySize(): int
    {
        return -1; // Variable-size (string)
    }

    protected function valueSize(): int
    {
        return 4; // Fixed-size int32
    }

    protected function readKey(ReadBuffer $buffer, int $offset): array
    {
        // String format: [4-byte size][data]
        $size = $buffer->readUInt32($offset);
        $value = $buffer->readString($offset + 4, $size);
        return [$value, 4 + $size];
    }

    protected function readValue(ReadBuffer $buffer, int $offset): array
    {
        $value = $buffer->readInt32($offset);
        return [$value, 4];
    }

    protected function writeKey(WriteBuffer $buffer, int $offset, mixed $key): int
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Key must be string');
        }

        // String format: [4-byte size][data]
        $size = strlen($key);
        $buffer->writeUInt32($offset, $size);
        $buffer->writeString($offset + 4, $key, $size);
        return 4 + $size;
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Value must be int');
        }

        $buffer->writeInt32($offset, $value);
        return 4;
    }
}
