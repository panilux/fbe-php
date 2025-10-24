<?php

declare(strict_types=1);

namespace FBE\Tests\Unit\Models;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Final\{FieldModelString, FieldModelInt32};

/**
 * Example struct model for testing Final format
 *
 * struct Person {
 *     string name;
 *     int32 age;
 * }
 *
 * Layout (Final format):
 * [4-byte name size + name data][4-byte age]
 *
 * No header! More compact than Standard format.
 */
final class PersonFinalModel extends StructModel
{
    private ?FieldModelString $nameField = null;
    private ?FieldModelInt32 $ageField = null;

    public function size(): int
    {
        $size = 0;

        // String field (inline, variable size)
        if ($this->nameField !== null) {
            $size += $this->nameField->size();
        } else {
            $size += 4; // Minimum size (empty string)
        }

        // Int32 field (fixed size)
        $size += 4;

        return $size;
    }

    public function extra(): int
    {
        return 0; // Final format: no extra data (all inline)
    }

    public function verify(): bool
    {
        // Final format: no header to verify
        // Always valid if buffer has enough data
        return true;
    }

    public function name(): FieldModelString
    {
        if ($this->nameField === null) {
            // Name starts at offset 0 (no header in Final format)
            $this->nameField = new FieldModelString($this->buffer, $this->offset);
        }
        return $this->nameField;
    }

    public function age(): FieldModelInt32
    {
        if ($this->ageField === null) {
            // Age comes after name field
            $nameSize = $this->name()->size();
            $this->ageField = new FieldModelInt32($this->buffer, $this->offset + $nameSize);
        }
        return $this->ageField;
    }
}
