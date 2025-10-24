<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Standard format Vector<String>
 *
 * More complex: Each string is pointer-based
 * Layout: [4-byte vector pointer] → [4-byte count][4-byte str1_ptr][4-byte str2_ptr]...
 */
final class FieldModelVectorString extends FieldModelVector
{
    private int $cachedStringExtra = 0;

    protected function elementSize(): int
    {
        return 4; // Each element is a pointer
    }

    public function extra(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            // Return vector structure + all string data
            $count = $this->count();
            return 4 + ($count * 4) + $this->cachedStringExtra;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        $count = $this->buffer->readUInt32($pointer);
        $extraSize = 4 + ($count * 4); // Vector structure

        // Add each string's extra data
        $elementOffset = $pointer + 4;
        for ($i = 0; $i < $count; $i++) {
            $stringPointer = $this->buffer->readUInt32($elementOffset);
            if ($stringPointer !== 0) {
                $stringSize = $this->buffer->readUInt32($stringPointer);
                $extraSize += 4 + $stringSize; // String header + data
            }
            $elementOffset += 4;
        }

        return $extraSize;
    }

    protected function readElement(ReadBuffer $buffer, int $offset): string
    {
        return $buffer->readStringPointer($offset);
    }

    protected function writeElement(WriteBuffer $buffer, int $offset, mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Vector element must be string');
        }
        $buffer->writeStringPointer($offset, $value);

        // Track string extra data
        $this->cachedStringExtra += 4 + strlen($value);
    }

    public function set(array $values): void
    {
        $this->cachedStringExtra = 0; // Reset before writing
        parent::set($values);
    }
}
