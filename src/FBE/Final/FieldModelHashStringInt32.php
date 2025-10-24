<?php

declare(strict_types=1);

namespace FBE\Final;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Hash<string, int32> field model (Final format)
 *
 * Binary: [4-byte count][inline key-value pairs...]
 * Example: hash {"a" => 1, "b" => 2} (unordered, inline)
 */
final class FieldModelHashStringInt32 extends FieldModelHash
{
    private int $actualSize = 4; // Minimum: count field

    protected function keySize(): int
    {
        return -1; // Variable size (string)
    }

    protected function valueSize(): int
    {
        return 4; // int32 = 4 bytes
    }

    public function size(): int
    {
        return $this->actualSize;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        // Read count
        $count = $this->buffer->readUInt32($this->offset);
        $this->actualSize = 4;

        if ($count === 0) {
            return [];
        }

        // Read key-value pairs (unordered, inline)
        $result = [];
        $offset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            // Read key (inline string)
            $keyResult = $this->readKey($this->buffer, $offset);
            $key = $keyResult['value'];
            $offset += $keyResult['bytesRead'];
            $this->actualSize += $keyResult['bytesRead'];

            // Read value (inline int32)
            $valueResult = $this->readValue($this->buffer, $offset);
            $value = $valueResult['value'];
            $offset += $valueResult['bytesRead'];
            $this->actualSize += $valueResult['bytesRead'];

            $result[$key] = $value;
        }

        return $result;
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $count = count($value);
        $this->actualSize = 4;

        // Write count
        $this->buffer->writeUInt32($this->offset, $count);

        // Write key-value pairs (preserve insertion order - unordered hash)
        $offset = $this->offset + 4;

        foreach ($value as $key => $val) {
            // Write key (inline string)
            $bytesWritten = $this->writeKey($this->buffer, $offset, $key);
            $offset += $bytesWritten;
            $this->actualSize += $bytesWritten;

            // Write value (inline int32)
            $bytesWritten = $this->writeValue($this->buffer, $offset, $val);
            $offset += $bytesWritten;
            $this->actualSize += $bytesWritten;
        }
    }

    protected function readKey(ReadBuffer $buffer, int $offset): array
    {
        // Read inline string: 4-byte size + data
        $size = $buffer->readUInt32($offset);
        $value = $size > 0 ? $buffer->readString($offset + 4, $size) : '';

        return ['value' => $value, 'bytesRead' => 4 + $size];
    }

    protected function readValue(ReadBuffer $buffer, int $offset): array
    {
        $value = $buffer->readInt32($offset);
        return ['value' => $value, 'bytesRead' => 4];
    }

    protected function writeKey(WriteBuffer $buffer, int $offset, mixed $key): int
    {
        return $buffer->writeStringInline($offset, $key);
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        $buffer->writeInt32($offset, $value);
        return 4;
    }
}
