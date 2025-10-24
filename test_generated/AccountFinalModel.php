<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Final\{
    FieldModelDouble,
    FieldModelInt32,
    FieldModelString,
    FieldModelTimestamp
};

/**
 * Account struct model (Final format)
 * 
 * ID: 1
 */
final class AccountFinalModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 24;
    }

    /**
     * Verify struct is valid
     */
    public function verify(): bool
    {
        return true; // Final format has no header
    }

    /**
     * Get id field model
     */
    public function id(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 0);
    }

    /**
     * Get username field model
     */
    public function username(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 4);
    }

    /**
     * Get balance field model
     */
    public function balance(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 8);
    }

    /**
     * Get createdAt field model
     */
    public function createdAt(): FieldModelTimestamp
    {
        return new FieldModelTimestamp($this->buffer, $this->offset + 16);
    }
}
