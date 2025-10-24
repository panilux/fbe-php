<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Set<int32> field model (Final format)
 *
 * Binary: [4-byte count][sorted unique int32 values...]
 * Example: set {3, 1, 2, 1} → {1, 2, 3} = 4 (count) + 12 (data) = 16 bytes
 */
final class FieldModelSetInt32 extends FieldModelSet
{
    private int $actualSize = 4; // Minimum: count field

    protected function elementSize(): int
    {
        return 4; // int32 = 4 bytes
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
        $this->actualSize = 4 + ($count * 4);

        if ($count === 0) {
            return [];
        }

        // Read sorted unique elements
        $result = [];
        $offset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->buffer->readInt32($offset);
            $offset += 4;
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
        $this->actualSize = 4 + ($count * 4);

        // Write count
        $this->buffer->writeUInt32($this->offset, $count);

        // Write sorted unique elements
        $offset = $this->offset + 4;
        foreach ($uniqueSorted as $element) {
            $this->buffer->writeInt32($offset, $element);
            $offset += 4;
        }
    }
}
