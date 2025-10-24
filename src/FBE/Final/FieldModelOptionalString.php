<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Final format Optional<String> (INLINE)
 *
 * Variable size: 1-byte flag + (4-byte size + data) if present
 */
final class FieldModelOptionalString extends FieldModelOptional
{
    protected function valueSize(): int
    {
        return -1; // Variable size
    }

    protected function readValue(ReadBuffer $buffer, int $offset): array
    {
        return $buffer->readStringInline($offset);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Optional value must be string');
        }
        return $buffer->writeStringInline($offset, $value);
    }
}
