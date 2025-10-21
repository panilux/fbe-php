<?php

namespace FBETest;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelInt32;
use FBE\FieldModelString;
use FBE\FieldModelInt8;
use FBE\FieldModelDouble;

class OrderModel
{
    private FieldModelInt32 $id;
    private FieldModelString $symbol;
    private FieldModelInt8 $side;
    private FieldModelInt8 $type;
    private FieldModelDouble $price;
    private FieldModelDouble $volume;
    
    public function __construct(
        private WriteBuffer|ReadBuffer $buffer,
        private int $offset = 0
    ) {
        // Field offsets (FBE format: 4-byte header + fields)
        $this->id = new FieldModelInt32($this->buffer, $this->offset + 4);
        $this->symbol = new FieldModelString($this->buffer, $this->offset + 8);
        $this->side = new FieldModelInt8($this->buffer, $this->offset + 12);
        $this->type = new FieldModelInt8($this->buffer, $this->offset + 13);
        $this->price = new FieldModelDouble($this->buffer, $this->offset + 14);
        $this->volume = new FieldModelDouble($this->buffer, $this->offset + 22);
    }
    
    public function serialize(Order $order): int
    {
        // FBE format: 4-byte size prefix + struct data
        $startOffset = $this->buffer->size;
        
        // Allocate header (4 bytes for size)
        $this->buffer->allocate(4);
        
        // Serialize fields
        $this->id->set($order->id);
        $this->symbol->set($order->symbol);
        $this->side->set($order->side);
        $this->type->set($order->type);
        $this->price->set($order->price);
        $this->volume->set($order->volume);
        
        // Write total size at the beginning
        $totalSize = $this->buffer->size - $startOffset;
        $this->buffer->writeUInt32($startOffset, $totalSize - 4);
        
        return $totalSize;
    }
    
    public function deserialize(): Order
    {
        return new Order(
            id: $this->id->get(),
            symbol: $this->symbol->get(),
            side: $this->side->get(),
            type: $this->type->get(),
            price: $this->price->get(),
            volume: $this->volume->get()
        );
    }
}

