<?php

declare(strict_types=1);

namespace FBE\Tests\Unit\Models;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{FieldModelString, FieldModelInt32};

/**
 * Example struct model for testing Standard format
 *
 * struct Person {
 *     string name;
 *     int32 age;
 * }
 *
 * Layout (Standard format):
 * [4-byte struct size][4-byte name pointer][4-byte age]
 */
final class PersonModel extends StructModel
{
    private const STRUCT_SIZE = 4 + 4 + 4; // header + name pointer + age

    private ?FieldModelString $nameField = null;
    private ?FieldModelInt32 $ageField = null;

    public function size(): int
    {
        return self::STRUCT_SIZE;
    }

    public function extra(): int
    {
        $extra = 0;

        if ($this->nameField !== null) {
            $extra += $this->nameField->extra();
        }

        return $extra;
    }

    public function verify(): bool
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return false;
        }

        // Read struct size from header
        $structSize = $this->buffer->readUInt32($this->offset);

        // Verify size matches expected
        return $structSize >= self::STRUCT_SIZE;
    }

    public function name(): FieldModelString
    {
        if ($this->nameField === null) {
            // Offset: 4 bytes (skip struct size header)
            $this->nameField = new FieldModelString($this->buffer, $this->offset + 4);
        }
        return $this->nameField;
    }

    public function age(): FieldModelInt32
    {
        if ($this->ageField === null) {
            // Offset: 4 (header) + 4 (name pointer)
            $this->ageField = new FieldModelInt32($this->buffer, $this->offset + 8);
        }
        return $this->ageField;
    }

    /**
     * Write struct header (size)
     */
    public function writeHeader(): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        // Write struct size to header
        $this->buffer->writeUInt32($this->offset, self::STRUCT_SIZE);
    }
}
