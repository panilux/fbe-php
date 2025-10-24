<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{FieldModel, ReadBuffer, WriteBuffer};

/**
 * Final format Bytes (INLINE: 4-byte size + data)
 */
final class FieldModelBytes extends FieldModel
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

        [$data, $consumed] = $this->buffer->readBytesInline($this->offset);
        $this->cachedSize = $consumed;

        return $data;
    }

    public function set(string $data): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $this->cachedSize = $this->buffer->writeBytesInline($this->offset, $data);
    }

    public function toJson(): string
    {
        return base64_encode($this->get());
    }

    public function fromJson(mixed $value): void
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected base64 string, got ' . get_debug_type($value));
        }
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            throw new \InvalidArgumentException('Invalid base64 string');
        }
        $this->set($decoded);
    }
}
