<?php
/**
 * ProductFinalModel - FinalModel without header
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/FBE/WriteBuffer.php';
require_once __DIR__ . '/../src/FBE/ReadBuffer.php';
require_once __DIR__ . '/../src/FBE/StructFinalModel.php';
require_once __DIR__ . '/ProductModel.php';  // For Product class

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\StructFinalModel;

class ProductFinalModel extends StructFinalModel
{
    protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int
    {
        $buffer->writeInt32($offset, $value->id);
        $offset += 4;
        
        $buffer->writeString($offset, $value->name);
        $offset += 4 + strlen($value->name);
        
        $buffer->writeDouble($offset, $value->price);
        $offset += 8;
        
        $buffer->writeInt32($offset, $value->quantity);
        $offset += 4;
        
        return $offset;
    }
    
    protected function deserializeStruct(ReadBuffer $buffer, int $offset)
    {
        $product = new Product();
        
        $product->id = $buffer->readInt32($offset);
        $offset += 4;
        
        $product->name = $buffer->readString($offset);
        $offset += 4 + strlen($product->name);
        
        $product->price = $buffer->readDouble($offset);
        $offset += 8;
        
        $product->quantity = $buffer->readInt32($offset);
        
        return $product;
    }
}

