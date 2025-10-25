<?php

declare(strict_types=1);

namespace Proto;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelInt32,
    FieldModelString,
    FieldModelUInt8
};

/**
 * Account struct model (Standard format)
 * 
 * ID: 3
 */
final class AccountModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 29; // Header + fields
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
        if ($structType !== 3) {
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
        $this->buffer->writeUInt32($this->offset + 4, 3);  // type ID
    }

    /**
     * Get id field model
     */
    public function id(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 8);
    }

    /**
     * Get name field model
     */
    public function name(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 12);
    }

    /**
     * Get state field model
     */
    public function state(): FieldModelUInt8
    {
        return new FieldModelUInt8($this->buffer, $this->offset + 16);
    }

    /**
     * Get wallet field model
     */
    public function wallet(): BalanceModel
    {
        return new BalanceModel($this->buffer, $this->offset + 17);
    }

    /**
     * Get asset field model
     */
    public function asset(): BalanceModel
    {
        return new BalanceModel($this->buffer, $this->offset + 21);
    }

    /**
     * Get orders field model
     */
    public function orders(): OrderModel
    {
        return new OrderModel($this->buffer, $this->offset + 25);
    }

    /**
     * Initialize fields with default values
     */
    public function initializeDefaults(): void
    {
        if (!($this->buffer instanceof \FBE\Common\WriteBuffer)) {
            throw new \RuntimeException('Cannot initialize defaults on ReadBuffer');
        }

        $this->state()->set(0x02 | unknown | invalid | broken);
    }
}
