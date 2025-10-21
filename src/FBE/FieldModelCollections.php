<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding collection field models (PHP 8.4+)
 * 
 * Collection field models: Vector, Array, Map, Set
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

// ============================================================================
// Vector<T> - Dynamic array
// ============================================================================

final class FieldModelVectorInt32 extends FieldModel
{
    public function size(): int { return 4; } // Pointer only

    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer == 0) return 0;
            $count = $this->buffer->readUInt32($pointer);
            return 4 + ($count * 4); // size + elements
        }
        return 0;
    }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readVectorInt32($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $values): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeVectorInt32($this->offset, $values);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Array[N] - Fixed-size array
// ============================================================================

final class FieldModelArrayInt32 extends FieldModel
{
    private int $count;

    public function __construct(WriteBuffer|ReadBuffer $buffer, int $offset, int $count)
    {
        parent::__construct($buffer, $offset);
        $this->count = $count;
    }

    public function size(): int { return $this->count * 4; }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readArrayInt32($this->offset, $this->count);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $values): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            if (count($values) !== $this->count) {
                throw new \InvalidArgumentException("Array size mismatch: expected {$this->count}, got " . count($values));
            }
            $this->buffer->writeArrayInt32($this->offset, $values);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Map<K,V> - Key-value pairs
// ============================================================================

final class FieldModelMapInt32 extends FieldModel
{
    public function size(): int { return 4; } // Pointer only

    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer == 0) return 0;
            $count = $this->buffer->readUInt32($pointer);
            return 4 + ($count * 8); // size + (key+value pairs)
        }
        return 0;
    }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readMapInt32($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $map): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeMapInt32($this->offset, $map);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Set<T> - Unique values
// ============================================================================

// ============================================================================
// String Collections
// ============================================================================

final class FieldModelVectorString extends FieldModel
{
    public function size(): int { return 4; }

    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer == 0) return 0;
            $size = $this->buffer->readUInt32($pointer);
            $total = 4;
            $currentOffset = $pointer + 4;
            for ($i = 0; $i < $size; $i++) {
                $len = $this->buffer->readUInt32($currentOffset);
                $total += 4 + $len;
                $currentOffset += 4 + $len;
            }
            return $total;
        }
        return 0;
    }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readVectorString($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $values): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeVectorString($this->offset, $values);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelArrayString extends FieldModel
{
    private int $count;

    public function __construct(WriteBuffer|ReadBuffer $buffer, int $offset, int $count)
    {
        parent::__construct($buffer, $offset);
        $this->count = $count;
    }

    public function size(): int
    {
        // Variable size, need to calculate
        if ($this->buffer instanceof ReadBuffer) {
            $total = 0;
            $currentOffset = $this->offset;
            for ($i = 0; $i < $this->count; $i++) {
                $len = $this->buffer->readUInt32($currentOffset);
                $total += 4 + $len;
                $currentOffset += 4 + $len;
            }
            return $total;
        }
        return 0;
    }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readArrayString($this->offset, $this->count);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $values): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            if (count($values) !== $this->count) {
                throw new \InvalidArgumentException("Array size mismatch");
            }
            $this->buffer->writeArrayString($this->offset, $values);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelSetInt32 extends FieldModel
{
    public function size(): int { return 4; } // Pointer only

    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer == 0) return 0;
            $count = $this->buffer->readUInt32($pointer);
            return 4 + ($count * 4); // size + elements
        }
        return 0;
    }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readSetInt32($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(array $values): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            // Ensure uniqueness
            $unique = array_values(array_unique($values));
            $this->buffer->writeSetInt32($this->offset, $unique);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

