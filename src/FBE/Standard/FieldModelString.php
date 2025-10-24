<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Standard format String (POINTER-BASED: 4-byte pointer → data)
 */
final class FieldModelString extends FieldModel
{
    private int $cachedExtra = 0;

    public function size(): int { return 4; } // Pointer only

    public function extra(): int
    {
        if ($this->buffer instanceof WriteBuffer) {
            return $this->cachedExtra;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        // Size at pointer location
        $size = $this->buffer->readUInt32($pointer);
        return 4 + $size; // size header + data
    }

    public function get(): string
    {
        return $this->buffer->readStringPointer($this->offset);
    }

    public function set(string $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeStringPointer($this->offset, $value);

        // Cache extra size: 4-byte size header + data
        $this->cachedExtra = 4 + strlen($value);
    }

    public function toJson(): string
    {
        return $this->get();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string, got ' . get_debug_type($value));
        }
        $this->set($value);
    }
}
