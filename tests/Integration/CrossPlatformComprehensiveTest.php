<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class CrossPlatformComprehensiveTest extends TestCase
{
    public function testInt32CrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, 123456);
        
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt32(0);
        $this->assertEquals(123456, $value);
    }
    
    public function testInt64CrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeInt64(0, 9876543210);
        
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt64(0);
        $this->assertEquals(9876543210, $value);
    }
    
    public function testFloatCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, 3.14);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readFloat(0);
        $this->assertEqualsWithDelta(3.14, $value, 0.001);
    }
    
    public function testDoubleCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeDouble(0, 2.718281828);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readDouble(0);
        $this->assertEqualsWithDelta(2.718281828, $value, 0.000000001);
    }
    
    public function testStringCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->writeString(0, 'Hello');
        
        $data = $writer->data();
        
        $reader = new ReadBuffer($data);
        $value = $reader->readString(0);
        $this->assertEquals('Hello', $value);
    }
    
    public function testVectorCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $vector = [10, 20, 30];
        $writer->writeVectorInt32(0, $vector);
        
        $data = $writer->data();
        
        $reader = new ReadBuffer($data);
        $value = $reader->readVectorInt32(0);
        $this->assertEquals($vector, $value);
    }
    
    public function testBoolCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeBool(0, true);
        $writer->writeBool(1, false);
        
        
        $reader = new ReadBuffer($writer->data());
        $this->assertTrue($reader->readBool(0));
        $this->assertFalse($reader->readBool(1));
    }
    
    public function testByteCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeByte(0, 0xFF);
        
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readByte(0);
        $this->assertEquals(0xFF, $value);
    }
    
    public function testUInt16CrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeUInt16(0, 0x1234);
        
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt16(0);
        $this->assertEquals(0x1234, $value);
    }
    
    public function testUInt32CrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeUInt32(0, 0x12345678);
        
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readUInt32(0);
        $this->assertEquals(0x12345678, $value);
    }
    
    public function testMapCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $map = [1 => 10, 2 => 20];
        $writer->writeMapInt32(0, $map);
        
        $data = $writer->data();
        
        $reader = new ReadBuffer($data);
        $value = $reader->readMapInt32(0);
        $this->assertEquals($map, $value);
    }
    
    public function testEmptyCollectionsCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->writeVectorInt32(0, []);
        
        $data = $writer->data();
        
        $reader = new ReadBuffer($data);
        $value = $reader->readVectorInt32(0);
        $this->assertEquals([], $value);
    }
    
    public function testMultipleTypesCrossPlatform(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(17);
        
        // Write multiple types sequentially
        $writer->writeBool(0, true);      // 1 byte
        $writer->writeInt32(1, 42);       // 4 bytes
        $writer->writeDouble(5, 3.14);    // 8 bytes
        $writer->writeUInt32(13, 999);    // 4 bytes
        
        $reader = new ReadBuffer($writer->data());
        $this->assertTrue($reader->readBool(0));
        $this->assertEquals(42, $reader->readInt32(1));
        $this->assertEqualsWithDelta(3.14, $reader->readDouble(5), 0.01);
        $this->assertEquals(999, $reader->readUInt32(13));
    }
}

