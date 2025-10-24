<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Fixed-size string array field model (Final format)
 *
 * Binary: N inline strings (each: 4-byte size + UTF-8 data)
 * Example: string[3] with ["A","BB","CCC"] = (4+1) + (4+2) + (4+3) = 18 bytes
 *
 * Note: Variable size! Cannot know total size without reading data.
 */
final class FieldModelArrayString extends FieldModelArray
{
    private int $actualSize = 0;

    public function size(): int
    {
        // Minimum size: N × 4 bytes (size prefixes for empty strings)
        // Actual size stored after set() or calculated during get()
        return $this->actualSize > 0 ? $this->actualSize : ($this->arraySize * 4);
    }

    public function get(): array
    {

        $result = [];
        $offset = $this->offset;
        $this->actualSize = 0;

        for ($i = 0; $i < $this->arraySize; $i++) {
            // Read inline string: 4-byte size + data
            $size = $this->buffer->readUInt32($offset);
            $offset += 4;

            if ($size > 0) {
                $result[] = $this->buffer->readString($offset, $size);
                $offset += $size;
            } else {
                $result[] = '';
            }

            $this->actualSize += 4 + $size;
        }

        return $result;
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $this->validateArraySize($value);

        $offset = $this->offset;
        $this->actualSize = 0;

        foreach ($value as $element) {
            $bytesWritten = $this->buffer->writeStringInline($offset, $element);
            $offset += $bytesWritten;
            $this->actualSize += $bytesWritten;
        }
    }
}
