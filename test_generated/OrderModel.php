<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Common\{ReadBuffer, WriteBuffer, StructModel};
use FBE\Standard\{
    FieldModelDouble,
    FieldModelInt32,
    FieldModelInt64,
    FieldModelString,
    FieldModelUuid
};

/**
 * Order struct model (Standard format)
 * 
 * ID: 2
 */
final class OrderModel extends StructModel
{

    /**
     * Get struct size in bytes
     */
    public function size(): int
    {
        return 36; // Header + 8 fields × 4 bytes
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
     * Get orderId field model
     */
    public function orderId(): FieldModelInt64
    {
        return new FieldModelInt64($this->buffer, $this->offset + 4);
    }

    /**
     * Get accountId field model
     */
    public function accountId(): FieldModelInt32
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
    public function side(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 16);
    }

    /**
     * Get type field model
     */
    public function type(): FieldModelInt32
    {
        return new FieldModelInt32($this->buffer, $this->offset + 20);
    }

    /**
     * Get price field model
     */
    public function price(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 24);
    }

    /**
     * Get quantity field model
     */
    public function quantity(): FieldModelDouble
    {
        return new FieldModelDouble($this->buffer, $this->offset + 28);
    }

    /**
     * Get correlationId field model
     */
    public function correlationId(): FieldModelUuid
    {
        return new FieldModelUuid($this->buffer, $this->offset + 32);
    }
}
