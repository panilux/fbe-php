<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};
use FBE\Types\Decimal;

/**
 * Standard format Decimal (INLINE: 16 bytes)
 */
final class FieldModelDecimal extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): Decimal
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Cannot read from WriteBuffer');
        }
        return $this->buffer->readDecimal($this->offset);
    }

    public function set(Decimal $decimal): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }
        $this->buffer->writeDecimal($this->offset, $decimal);
    }
}
