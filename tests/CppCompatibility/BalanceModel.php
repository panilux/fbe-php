<?php

declare(strict_types=1);

namespace Proto;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelDouble,
    FieldModelString
};

/**
 * Balance struct model (Standard format)
 * 
 * ID: 2
 */
final class BalanceModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 20; // Header + fields
    }

    /**
     * Verify struct is valid
     * Checks: 8-byte header (size + type)
     */
    public function verify(): bool
    {
        if (!($this->buffer instanceof \FBE\Common\ReadBuffer)) {
            return false;
        }

        // Verify 8-byte header (FBE C++ spec)
        $structSize = $this->buffer->readUInt32($this->offset);
        $structType = $this->buffer->readUInt32($this->offset + 4);

        // Check size and type
        if ($structSize < $this->size()) {
            return false;
        }
        if ($structType !== 2) {
            return false; // Type mismatch
        }
        return true;
    }

    /**
     * Write struct header (Standard format)
     * Header: 8 bytes (4-byte size + 4-byte type)
     */
    public function writeHeader(): void
    {
        if (!($this->buffer instanceof \FBE\Common\WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        // Write 8-byte header (FBE C++ spec)
        $this->buffer->writeUInt32($this->offset, $this->size());      // size
        $this->buffer->writeUInt32($this->offset + 4, 2);  // type ID
    }

    /**
     * Get currency field model
     */
    public function currency(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 8);
    }

    /**
     * Get amount field model
     */
    public function amount(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 12);
    }

    /**
     * Initialize fields with default values
     */
    public function initializeDefaults(): void
    {
        if (!($this->buffer instanceof \FBE\Common\WriteBuffer)) {
            throw new \RuntimeException('Cannot initialize defaults on ReadBuffer');
        }

        $this->amount()->set(0.0);
    }
}
