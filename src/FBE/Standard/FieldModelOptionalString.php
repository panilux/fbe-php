<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Standard format Optional<String>
 *
 * Note: String is pointer-based, so extra() includes string data
 */
final class FieldModelOptionalString extends FieldModelOptional
{
    private int $cachedExtra = 0;

    protected function valueSize(): int
    {
        return 4; // Pointer
    }

    public function extra(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedExtra;
        }

        if (!$this->hasValue()) {
            return 0;
        }

        // Read string size at pointer location
        $stringPointer = $this->buffer->readUInt32($this->offset + 1);
        if ($stringPointer === 0) {
            return 0;
        }

        $size = $this->buffer->readUInt32($stringPointer);
        return 4 + $size; // size header + data
    }

    protected function readValue(ReadBuffer $buffer, int $offset): string
    {
        return $buffer->readStringPointer($offset);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Optional value must be string');
        }
        $buffer->writeStringPointer($offset, $value);
        $this->cachedExtra = 4 + strlen($value);
    }

    public function set(mixed $value): void
    {
        if ($value === null) {
            $this->cachedExtra = 0;
        }
        parent::set($value);
    }
}
