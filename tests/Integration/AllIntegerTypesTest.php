<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class AllIntegerTypesTest extends TestCase
{
    public function testInt8(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeInt8(0, -128);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt8(0);
        
        $this->assertEquals(-128, $value);
    }
    
    public function testInt8Max(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeInt8(0, 127);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt8(0);
        
        $this->assertEquals(127, $value);
    }
    
    public function testUInt8(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeUInt8(0, 255);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt8(0);
        
        $this->assertEquals(255, $value);
    }
    
    public function testInt16(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeInt16(0, -32768);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt16(0);
        
        $this->assertEquals(-32768, $value);
    }
    
    public function testInt16Max(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeInt16(0, 32767);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt16(0);
        
        $this->assertEquals(32767, $value);
    }
    
    public function testUInt16(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeUInt16(0, 65535);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt16(0);
        
        $this->assertEquals(65535, $value);
    }
    
    public function testUInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeUInt32(0, 4294967295);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt32(0);
        
        $this->assertEquals(4294967295, $value);
    }
    
    public function testInt64(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeInt64(0, -9223372036854775807);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt64(0);
        
        $this->assertEquals(-9223372036854775807, $value);
    }
    
    public function testInt64Max(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeInt64(0, 9223372036854775807);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt64(0);
        
        $this->assertEquals(9223372036854775807, $value);
    }
    
    public function testUInt64(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeUInt64(0, 9223372036854775807); // Max safe int in PHP
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt64(0);
        
        $this->assertEquals(9223372036854775807, $value);
    }
    
    public function testByte(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeByte(0, 255);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readByte(0);
        
        $this->assertEquals(255, $value);
    }
    
    public function testByteZero(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeByte(0, 0);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readByte(0);
        
        $this->assertEquals(0, $value);
    }
}

