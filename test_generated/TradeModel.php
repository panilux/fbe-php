<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelDouble,
    FieldModelInt64,
    FieldModelString,
    FieldModelTimestamp
};

/**
 * Trade struct model (Standard format)
 * 
 * ID: 3
 */
final class TradeModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 28; // Header + 6 fields × 4 bytes
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
     * Get tradeId field model
     */
    public function tradeId(): FieldModelInt64
    {
        return new FieldModelInt64($this->buffer, $this->offset + 4);
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
        return new FieldModelString($this->buffer, $this->offset + 12);
    }

    /**
     * Get price field model
     */
    public function price(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 16);
    }

    /**
     * Get quantity field model
     */
    public function quantity(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 20);
    }

    /**
     * Get executedAt field model
     */
    public function executedAt(): FieldModelTimestamp
    {
        return new FieldModelTimestamp($this->buffer, $this->offset + 24);
    }
}
