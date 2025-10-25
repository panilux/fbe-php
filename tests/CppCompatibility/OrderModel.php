<?php

declare(strict_types=1);

namespace Proto;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelDouble,
    FieldModelInt32,
    FieldModelString,
    FieldModelUInt8
};

/**
 * Order struct model (Standard format)
 * 
 * ID: 1
 */
final class OrderModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 34; // Header + fields
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
        if ($structType !== 1) {
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
        $this->buffer->writeUInt32($this->offset + 4, 1);  // type ID
    }

    /**
     * Get id field model
     */
    public function id(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 8);
    }

    /**
     * Get symbol field model
     */
    public function symbol(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 12);
    }

    /**
     * Get side field model
     */
    public function side(): FieldModelUInt8
    {
        return new FieldModelUInt8($this->buffer, $this->offset + 16);
    }

    /**
     * Get type field model
     */
    public function type(): FieldModelUInt8
    {
        return new FieldModelUInt8($this->buffer, $this->offset + 17);
    }

    /**
     * Get price field model
     */
    public function price(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 18);
    }

    /**
     * Get volume field model
     */
    public function volume(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 26);
    }

    /**
     * Initialize fields with default values
     */
    public function initializeDefaults(): void
    {
        if (!($this->buffer instanceof \FBE\Common\WriteBuffer)) {
            throw new \RuntimeException('Cannot initialize defaults on ReadBuffer');
        }

        $this->price()->set(0.0);
        $this->volume()->set(0.0);
    }
}
