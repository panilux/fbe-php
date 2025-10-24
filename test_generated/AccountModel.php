<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelDouble,
    FieldModelInt32,
    FieldModelString,
    FieldModelTimestamp
};

/**
 * Account struct model (Standard format)
 * 
 * ID: 1
 */
final class AccountModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 20; // Header + 4 fields × 4 bytes
    }

    /**
     * Verify struct is valid
     */
    public function verify(): bool
    {
        if (!($this->buffer instanceof \FBE\Common\ReadBuffer)) {
            return false;
        }

        $structSize = $this->buffer->readUInt32($this->offset);
        return $structSize >= $this->size();
    }

    /**
     * Write struct header (Standard format)
     */
    public function writeHeader(): void
    {
        if (!($this->buffer instanceof \FBE\Common\WriteBuffer)) {
            throw new \RuntimeException('Cannot write to ReadBuffer');
        }

        $this->buffer->writeUInt32($this->offset, $this->size());
    }

    /**
     * Get id field model
     */
    public function id(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 4);
    }

    /**
     * Get username field model
     */
    public function username(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 8);
    }

    /**
     * Get balance field model
     */
    public function balance(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 12);
    }

    /**
     * Get createdAt field model
     */
    public function createdAt(): FieldModelTimestamp
    {
        return new FieldModelTimestamp($this->buffer, $this->offset + 16);
    }
}
