<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Standard format Map<String, String>
 *
 * Layout: [4-byte pointer] → [4-byte count][key1][value1][key2][value2]...
 * Each key/value: [4-byte size][UTF-8 data]
 */
final class FieldModelMapStringString extends FieldModelMap
{
    protected function keySize(): int
    {
        return -1; // Variable-size (string)
    }

    protected function valueSize(): int
    {
        return -1; // Variable-size (string)
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
        // String format: [4-byte size][data]
        $size = $buffer->readUInt32($offset);
        $value = $buffer->readString($offset + 4, $size);
        return [$value, 4 + $size];
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
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Value must be string');
        }

        // String format: [4-byte size][data]
        $size = strlen($value);
        $buffer->writeUInt32($offset, $size);
        $buffer->writeString($offset + 4, $value, $size);
        return 4 + $size;
    }
}
