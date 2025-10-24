<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format Optional<T>
 *
 * Layout: [1-byte has_value][value if present]
 *
 * Example: Optional<Int32>
 * - Offset 0: 1-byte flag (0 = null, 1 = has value)
 * - Offset 1: 4-byte int32 (if has_value = 1)
 */
abstract class FieldModelOptional extends FieldModel
{
    private bool $cachedHasValue = false;

    public function size(): int
    {
        return 1 + $this->valueSize();
    }

    public function extra(): int
    {
        return 0; // Optional fields are inline in Standard format
    }

    /**
     * Get the size of the wrapped value
     */
    abstract protected function valueSize(): int;

    /**
     * Read the value from buffer
     */
    abstract protected function readValue(ReadBuffer $buffer, int $offset): mixed;

    /**
     * Write the value to buffer
     */
    abstract protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): void;

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

        if (!$this->hasValue()) {
            return null;
        }

        return $this->readValue($this->buffer, $this->offset + 1);
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
        } else {
            // Write has_value = true
            $this->buffer->writeBool($this->offset, true);
            $this->writeValue($this->buffer, $this->offset + 1, $value);
            $this->cachedHasValue = true;
        }
    }
}
