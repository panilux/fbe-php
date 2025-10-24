<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

final class FieldModelInt32 extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        return $this->buffer->readInt32($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeInt32($this->offset, $value);
    }

    /**
     * Convert to JSON-encodable value
     */
    public function toJson(): int
    {
        // Buffer base class now supports read operations for both WriteBuffer and ReadBuffer
        return $this->buffer->readInt32($this->offset);
    }

    /**
     * Set value from JSON-decoded data
     *
     * @param mixed $value JSON-decoded value (int expected)
     */
    public function fromJson(mixed $value): void
    {
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Expected int, got ' . get_debug_type($value));
        }
        $this->set($value);
    }
}
