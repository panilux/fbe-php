<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Set<string> field model (Final format)
 *
 * Binary: [4-byte count][inline strings...]
 * Example: set {"cat", "dog", "cat"} → {"cat", "dog"} (sorted, unique)
 */
final class FieldModelSetString extends FieldModelSet
{
    private int $actualSize = 4; // Minimum: count field

    protected function elementSize(): int
    {
        return -1; // Variable size
    }

    public function size(): int
    {
        return $this->actualSize;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        // Read count
        $count = $this->buffer->readUInt32($this->offset);
        $this->actualSize = 4;

        if ($count === 0) {
            return [];
        }

        // Read inline strings
        $result = [];
        $offset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
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

        // Deduplicate and sort
        $uniqueSorted = $this->normalizeSet($value);

        $count = count($uniqueSorted);
        $this->actualSize = 4;

        // Write count
        $this->buffer->writeUInt32($this->offset, $count);

        // Write inline strings
        $offset = $this->offset + 4;
        foreach ($uniqueSorted as $str) {
            $bytesWritten = $this->buffer->writeStringInline($offset, $str);
            $offset += $bytesWritten;
            $this->actualSize += $bytesWritten;
        }
    }
}
