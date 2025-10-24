<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Final\{
    FieldModelDouble,
    FieldModelInt64,
    FieldModelString,
    FieldModelTimestamp
};

/**
 * Trade struct model (Final format)
 * 
 * ID: 3
 */
final class TradeFinalModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 44;
    }

    /**
     * Verify struct is valid
     */
    public function verify(): bool
    {
        return true; // Final format has no header
    }

    /**
     * Get tradeId field model
     */
    public function tradeId(): FieldModelInt64
    {
        return new FieldModelInt64($this->buffer, $this->offset + 0);
    }

    /**
     * Get orderId field model
     */
    public function orderId(): FieldModelInt64
    {
        return new FieldModelInt64($this->buffer, $this->offset + 8);
    }

    /**
     * Get symbol field model
     */
    public function symbol(): FieldModelString
    {
        return new FieldModelString($this->buffer, $this->offset + 16);
    }

    /**
     * Get price field model
     */
    public function price(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 20);
    }

    /**
     * Get quantity field model
     */
    public function quantity(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 28);
    }

    /**
     * Get executedAt field model
     */
    public function executedAt(): FieldModelTimestamp
    {
        return new FieldModelTimestamp($this->buffer, $this->offset + 36);
    }
}
