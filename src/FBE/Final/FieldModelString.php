<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format String (INLINE: 4-byte size + data)
 */
final class FieldModelString extends FieldModel
{
    private int $cachedSize = 4; // Default: just size header

    public function size(): int
    {
        return $this->cachedSize;
    }

    public function extra(): int
    {
        return 0; // Already included in size()
    }

    public function get(): string
    {

        [$value, $consumed] = $this->buffer->readStringInline($this->offset);
        $this->cachedSize = $consumed;

        return $value;
    }

    public function set(string $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $this->cachedSize = $this->buffer->writeStringInline($this->offset, $value);
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
