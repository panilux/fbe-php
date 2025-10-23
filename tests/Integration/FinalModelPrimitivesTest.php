<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class FinalModelPrimitivesTest extends TestCase
{
    public function testFinalModelBool(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeBool(0, true);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertTrue($reader->readBool(0));
    }
    
    public function testFinalModelByte(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeByte(0, 255);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(255, $reader->readByte(0));
    }
    
    public function testFinalModelChar(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeChar(0, ord('Z'));
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(ord('Z'), $reader->readChar(0));
    }
    
    public function testFinalModelWChar(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeWChar(0, mb_ord('中'));
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(mb_ord('中'), $reader->readWChar(0));
    }
    
    public function testFinalModelInt8(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeInt8(0, -128);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(-128, $reader->readInt8(0));
    }
    
    public function testFinalModelInt16(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeInt16(0, -32768);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(-32768, $reader->readInt16(0));
    }
    
    public function testFinalModelInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, -2147483648);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(-2147483648, $reader->readInt32(0));
    }
    
    public function testFinalModelInt64(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeInt64(0, -9223372036854775807);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(-9223372036854775807, $reader->readInt64(0));
    }
    
    public function testFinalModelUInt8(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeUInt8(0, 255);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(255, $reader->readUInt8(0));
    }
    
    public function testFinalModelUInt16(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(2);
        $writer->writeUInt16(0, 65535);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(65535, $reader->readUInt16(0));
    }
    
    public function testFinalModelUInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeUInt32(0, 4294967295);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(4294967295, $reader->readUInt32(0));
    }
    
    public function testFinalModelUInt64(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeUInt64(0, 9223372036854775807);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(9223372036854775807, $reader->readUInt64(0));
    }
    
    public function testFinalModelFloat(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, 3.14159);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEqualsWithDelta(3.14159, $reader->readFloat(0), 0.00001);
    }
    
    public function testFinalModelDouble(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeDouble(0, 2.718281828459045);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEqualsWithDelta(2.718281828459045, $reader->readDouble(0), 0.000000000000001);
    }
    
    public function testFinalModelString(): void
    {
        $writer = new WriteBuffer();
        $writer->writeString(0, 'Hello FBE');
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals('Hello FBE', $reader->readString(0));
    }
    
    public function testFinalModelBytes(): void
    {
        $writer = new WriteBuffer();
        $bytes = pack('C*', 0xCA, 0xFE, 0xBA, 0xBE);
        $writer->writeBytes(0, $bytes);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals($bytes, $reader->readBytes(0));
    }
    
    public function testFinalModelDecimal(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $writer->writeDecimal(0, 999999, 3, true);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readDecimal(0);
        
        $this->assertEquals(999999, $result['value']);
        $this->assertEquals(3, $result['scale']);
        $this->assertTrue($result['negative']);
    }
    
    public function testFinalModelUUID(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $uuid = '123e4567-e89b-12d3-a456-426614174000';
        $writer->writeUUID(0, $uuid);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals($uuid, $reader->readUUID(0));
    }
    
    public function testFinalModelTimestamp(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $timestamp = 1609459200;
        $writer->writeTimestamp(0, $timestamp);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals($timestamp, $reader->readTimestamp(0));
    }
}

