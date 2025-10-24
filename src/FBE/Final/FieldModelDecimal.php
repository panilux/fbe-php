<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};
use FBE\Types\Decimal;

/**
 * Final format Decimal (INLINE: 16 bytes) - Same as Standard
 */
final class FieldModelDecimal extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): Decimal
    {
        return $this->buffer->readDecimal($this->offset);
    }

    public function set(Decimal $decimal): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeDecimal($this->offset, $decimal);
    }

    public function toJson(): string
    {
        return $this->get()->toString();
    }

    public function fromJson(mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected decimal string, got ' . get_debug_type($value));
        }
        $this->set(Decimal::fromString($value));
    }
}
