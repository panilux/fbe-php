<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{FieldModelFlags, ReadBuffer, WriteBuffer};

/**
 * State flags field model (Standard format)
 *
 * Binary: 1 byte (underlying type: byte)
 * Example: State::INITIALIZED | State::CALCULATED = 0x03
 *
 * Note: Identical to Final format (fixed-size, always inline)
 */
final class FieldModelState extends FieldModelFlags
{
    protected function underlyingSize(): int
    {
        return 1; // byte = 1 byte
    }

    public function get(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        return $this->buffer->readUInt8($this->offset);
    }

    public function set(int $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $this->buffer->writeUInt8($this->offset, $value);
    }
}
