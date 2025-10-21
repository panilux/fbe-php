<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding primitive field models (PHP 8.4+)
 *
 * All primitive type field models with modern PHP 8.4 features.
 */

// ============================================================================
// Boolean
// ============================================================================

final class FieldModelBool extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): bool
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readBool($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(bool $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeBool($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Signed Integers
// ============================================================================

final class FieldModelInt8 extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readInt8($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeInt8($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelInt16 extends FieldModel
{
    public function size(): int { return 2; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readInt16($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeInt16($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelInt32 extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readInt32($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeInt32($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelInt64 extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readInt64($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeInt64($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Unsigned Integers
// ============================================================================

final class FieldModelUInt8 extends FieldModel
{
    public function size(): int { return 1; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readUInt8($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeUInt8($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelUInt16 extends FieldModel
{
    public function size(): int { return 2; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readUInt16($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeUInt16($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelUInt32 extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readUInt32($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeUInt32($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelUInt64 extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readUInt64($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeUInt64($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Floating Point
// ============================================================================

final class FieldModelFloat extends FieldModel
{
    public function size(): int { return 4; }

    public function get(): float
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readFloat($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(float $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeFloat($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelDouble extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): float
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readDouble($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(float $value): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeDouble($this->offset, $value);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

// ============================================================================
// Complex Types
// ============================================================================

final class FieldModelTimestamp extends FieldModel
{
    public function size(): int { return 8; }

    public function get(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readTimestamp($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $nanoseconds): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeTimestamp($this->offset, $nanoseconds);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelUuid extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): string
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readUuid($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(string $uuid): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeUuid($this->offset, $uuid);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelBytes extends FieldModel
{
    public function size(): int { return 4; }

    public function extra(): int
    {
        if ($this->buffer instanceof ReadBuffer) {
            $size = $this->buffer->readUInt32($this->offset);
            return $size;
        }
        return 0;
    }

    public function get(): string
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readBytes($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(string $data): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeBytes($this->offset, $data);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

final class FieldModelDecimal extends FieldModel
{
    public function size(): int { return 16; }

    public function get(): array
    {
        if ($this->buffer instanceof ReadBuffer) {
            return $this->buffer->readDecimal($this->offset);
        }
        throw new \RuntimeException("Cannot read from WriteBuffer");
    }

    public function set(int $value, int $scale, bool $negative): void
    {
        if ($this->buffer instanceof WriteBuffer) {
            $this->buffer->writeDecimal($this->offset, $value, $scale, $negative);
            return;
        }
        throw new \RuntimeException("Cannot write to ReadBuffer");
    }
}

