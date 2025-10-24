<?php

declare(strict_types=1);

namespace FBE\V2\Final;

use FBE\V2\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format Optional<T> (INLINE)
 *
 * Layout: [1-byte has_value][value if present]
 *
 * For variable-size values (strings), size changes dynamically
 */
abstract class FieldModelOptional extends FieldModel
{
    private bool $cachedHasValue = false;
    private int $cachedSize = 1; // Default: just has_value flag

    public function size(): int
    {
        return $this->cachedSize;
    }

    public function extra(): int
    {
        return 0; // All inline
    }

    /**
     * Get fixed value size (return -1 for variable-size like strings)
     */
    abstract protected function valueSize(): int;

    /**
     * Read value and return [value, bytes_consumed]
     */
    abstract protected function readValue(ReadBuffer $buffer, int $offset): array;

    /**
     * Write value and return bytes_consumed
     */
    abstract protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int;

    /**
     * Check if value is present
     */
    public function hasValue(): bool
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedHasValue;
        }

        return $this->buffer->readBool($this->offset);
    }

    /**
     * Get optional value (returns null if not present)
     */
    public function get(): mixed
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }

        $hasValue = $this->buffer->readBool($this->offset);
        $this->cachedHasValue = $hasValue;

        if (!$hasValue) {
            // Update cached size for fixed-size types
            $fixedSize = $this->valueSize();
            if ($fixedSize > 0) {
                $this->cachedSize = 1 + $fixedSize;
            } else {
                $this->cachedSize = 1; // No value
            }
            return null;
        }

        [$value, $consumed] = $this->readValue($this->buffer, $this->offset + 1);
        $this->cachedSize = 1 + $consumed;

        return $value;
    }

    /**
     * Set optional value (use null to clear)
     */
    public function set(mixed $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        if ($value === null) {
            // Write has_value = false
            $this->buffer->writeBool($this->offset, false);
            $this->cachedHasValue = false;

            // For fixed-size types, still reserve space
            $fixedSize = $this->valueSize();
            if ($fixedSize > 0) {
                $this->cachedSize = 1 + $fixedSize;
            } else {
                $this->cachedSize = 1;
            }
        } else {
            // Write has_value = true
            $this->buffer->writeBool($this->offset, true);
            $consumed = $this->writeValue($this->buffer, $this->offset + 1, $value);
            $this->cachedHasValue = true;
            $this->cachedSize = 1 + $consumed;
        }
    }
}
