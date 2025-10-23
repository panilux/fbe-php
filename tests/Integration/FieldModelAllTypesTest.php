<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelByte;
use FBE\FieldModelChar;
use FBE\FieldModelWChar;
use FBE\FieldModelInt8;
use FBE\FieldModelInt16;
use FBE\FieldModelInt64;
use FBE\FieldModelUInt8;
use FBE\FieldModelUInt16;
use FBE\FieldModelUInt32;
use FBE\FieldModelUInt64;
use FBE\FieldModelBytes;
use FBE\FieldModelDecimal;
use FBE\FieldModelUUID;
use FBE\FieldModelTimestamp;

final class FieldModelAllTypesTest extends TestCase
{
    public function testFieldModelByte(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelByte($buffer, 0);
        $model->set(255);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelByte($reader, 0);
        $this->assertEquals(255, $readModel->get());
    }
    
    public function testFieldModelChar(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelChar($buffer, 0);
        $model->set(ord('X'));
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelChar($reader, 0);
        $this->assertEquals(ord('X'), $readModel->get());
    }
    
    public function testFieldModelWChar(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelWChar($buffer, 0);
        $model->set(mb_ord('€'));
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelWChar($reader, 0);
        $this->assertEquals(mb_ord('€'), $readModel->get());
    }
    
    public function testFieldModelInt8(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelInt8($buffer, 0);
        $model->set(-128);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelInt8($reader, 0);
        $this->assertEquals(-128, $readModel->get());
    }
    
    public function testFieldModelInt16(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelInt16($buffer, 0);
        $model->set(-32768);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelInt16($reader, 0);
        $this->assertEquals(-32768, $readModel->get());
    }
    
    public function testFieldModelInt64(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(20);
        $model = new FieldModelInt64($buffer, 0);
        $model->set(-9223372036854775807);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelInt64($reader, 0);
        $this->assertEquals(-9223372036854775807, $readModel->get());
    }
    
    public function testFieldModelUInt8(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelUInt8($buffer, 0);
        $model->set(255);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelUInt8($reader, 0);
        $this->assertEquals(255, $readModel->get());
    }
    
    public function testFieldModelUInt16(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelUInt16($buffer, 0);
        $model->set(65535);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelUInt16($reader, 0);
        $this->assertEquals(65535, $readModel->get());
    }
    
    public function testFieldModelUInt32(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $model = new FieldModelUInt32($buffer, 0);
        $model->set(4294967295);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelUInt32($reader, 0);
        $this->assertEquals(4294967295, $readModel->get());
    }
    
    public function testFieldModelUInt64(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(20);
        $model = new FieldModelUInt64($buffer, 0);
        $model->set(9223372036854775807);
        
        $reader = new ReadBuffer($buffer->data());
        $readModel = new FieldModelUInt64($reader, 0);
        $this->assertEquals(9223372036854775807, $readModel->get());
    }
    

}

