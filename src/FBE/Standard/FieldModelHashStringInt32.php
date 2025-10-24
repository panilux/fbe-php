<?php

declare(strict_types=1);

namespace FBE\Standard;

use FBE\Common\{ReadBuffer, WriteBuffer};

/**
 * Hash<string, int32> field model (Standard format)
 *
 * Binary: [4-byte pointer] → [4-byte count][key pointers...][value int32s...][string data...]
 * Example: hash {"a" => 1, "b" => 2} (unordered)
 */
final class FieldModelHashStringInt32 extends FieldModelHash
{
    private int $extraSize = 0;

    protected function keySize(): int
    {
        return -1; // Variable size (string)
    }

    protected function valueSize(): int
    {
        return 4; // int32 = 4 bytes
    }

    public function extra(): int
    {
        return $this->extraSize;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException('Buffer is not readable');
        }

        // Read main pointer
        $mainPointer = $this->buffer->readUInt32($this->offset);

        if ($mainPointer === 0) {
            $this->extraSize = 0;
            return [];
        }

        // Read count
        $count = $this->buffer->readUInt32($mainPointer);

        if ($count === 0) {
            $this->extraSize = 4;
            return [];
        }

        // Read key-value pairs (unordered)
        $result = [];
        $offset = $mainPointer + 4;
        $totalDataSize = 0;

        for ($i = 0; $i < $count; $i++) {
            // Read key (pointer to string)
            $keyResult = $this->readKey($this->buffer, $offset);
            $key = $keyResult['value'];
            $offset += $keyResult['bytesRead'];
            $totalDataSize += $keyResult['bytesRead'];

            // Read value (inline int32)
            $valueResult = $this->readValue($this->buffer, $offset);
            $value = $valueResult['value'];
            $offset += $valueResult['bytesRead'];

            $result[$key] = $value;

            // Track string data size
            if ($key !== '') {
                $totalDataSize += 4 + strlen($key);
            }
        }

        $this->extraSize = 4 + $totalDataSize;

        return $result;
    }

    public function set(array $value): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Buffer is not writable');
        }

        $count = count($value);

        // Allocate space for count + entries
        $entriesSize = $count * (4 + 4); // N × (key pointer + value int32)
        $mainPointer = $this->buffer->allocate(4 + $entriesSize);

        // Write main pointer
        $this->buffer->writeUInt32($this->offset, $mainPointer);

        // Write count
        $this->buffer->writeUInt32($mainPointer, $count);

        // Write key-value pairs (preserve insertion order - unordered hash)
        $offset = $mainPointer + 4;
        $totalDataSize = 0;

        foreach ($value as $key => $val) {
            // Write key (string pointer)
            $bytesWritten = $this->writeKey($this->buffer, $offset, $key);
            $offset += $bytesWritten;
            $totalDataSize += $bytesWritten + 4 + strlen($key);

            // Write value (inline int32)
            $bytesWritten = $this->writeValue($this->buffer, $offset, $val);
            $offset += $bytesWritten;
        }

        $this->extraSize = 4 + $entriesSize + $totalDataSize;
    }

    protected function readKey(ReadBuffer $buffer, int $offset): array
    {
        // Read string pointer
        $pointer = $buffer->readUInt32($offset);

        if ($pointer === 0) {
            return ['value' => '', 'bytesRead' => 4];
        }

        // Read string at pointer
        $size = $buffer->readUInt32($pointer);
        $value = $buffer->readString($pointer + 4, $size);

        return ['value' => $value, 'bytesRead' => 4];
    }

    protected function readValue(ReadBuffer $buffer, int $offset): array
    {
        $value = $buffer->readInt32($offset);
        return ['value' => $value, 'bytesRead' => 4];
    }

    protected function writeKey(WriteBuffer $buffer, int $offset, mixed $key): int
    {
        $buffer->writeStringPointer($offset, $key);
        return 4; // Pointer size
    }

    protected function writeValue(WriteBuffer $buffer, int $offset, mixed $value): int
    {
        $buffer->writeInt32($offset, $value);
        return 4;
    }
}
