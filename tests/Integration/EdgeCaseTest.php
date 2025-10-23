<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class EdgeCaseTest extends TestCase
{
    public function testEmptyString(): void
    {
        $writer = new WriteBuffer();
        $writer->writeString(0, '');
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readString(0);
        
        $this->assertEquals('', $value);
    }
    
    public function testVeryLongString(): void
    {
        $writer = new WriteBuffer();
        $longString = str_repeat('A', 10000);
        $writer->writeString(0, $longString);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readString(0);
        
        $this->assertEquals($longString, $value);
    }
    
    public function testEmptyVector(): void
    {
        $writer = new WriteBuffer();
        $writer->writeVectorInt32(0, []);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readVectorInt32(0);
        
        $this->assertEquals([], $value);
    }
    
    public function testLargeVector(): void
    {
        $writer = new WriteBuffer();
        $largeVector = range(1, 10000);
        $writer->writeVectorInt32(0, $largeVector);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readVectorInt32(0);
        
        $this->assertEquals($largeVector, $value);
    }
    
    public function testEmptyMap(): void
    {
        $writer = new WriteBuffer();
        $writer->writeMapInt32(0, []);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readMapInt32(0);
        
        $this->assertEquals([], $value);
    }
    
    public function testMapWithManyEntries(): void
    {
        $writer = new WriteBuffer();
        $map = [];
        for ($i = 0; $i < 1000; $i++) {
            $map[$i] = $i * 10;
        }
        $writer->writeMapInt32(0, $map);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readMapInt32(0);
        
        $this->assertEquals($map, $value);
    }
    
    public function testInt32Zero(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, 0);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt32(0);
        
        $this->assertEquals(0, $value);
    }
    
    public function testInt32MinValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, -2147483648);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt32(0);
        
        $this->assertEquals(-2147483648, $value);
    }
    
    public function testInt32MaxValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, 2147483647);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readInt32(0);
        
        $this->assertEquals(2147483647, $value);
    }
    
    public function testFloatZero(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, 0.0);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readFloat(0);
        
        $this->assertEquals(0.0, $value);
    }
    
    public function testFloatNegative(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, -123.456);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readFloat(0);
        
        $this->assertEqualsWithDelta(-123.456, $value, 0.001);
    }
    
    public function testDoubleVerySmall(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeDouble(0, 0.000000000001);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readDouble(0);
        
        $this->assertEqualsWithDelta(0.000000000001, $value, 0.0000000000001);
    }
    
    public function testDoubleVeryLarge(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $writer->writeDouble(0, 1234567890123.456);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readDouble(0);
        
        $this->assertEqualsWithDelta(1234567890123.456, $value, 0.001);
    }
    
    public function testBytesEmpty(): void
    {
        $writer = new WriteBuffer();
        $writer->writeBytes(0, '');
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readBytes(0);
        
        $this->assertEquals('', $value);
    }
    
    public function testBytesLarge(): void
    {
        $writer = new WriteBuffer();
        $bytes = str_repeat("\x00\xFF", 5000);
        $writer->writeBytes(0, $bytes);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readBytes(0);
        
        $this->assertEquals($bytes, $value);
    }
    
    public function testUnicodeString(): void
    {
        $writer = new WriteBuffer();
        $unicode = "Hello 世界 🌍 مرحبا Привет";
        $writer->writeString(0, $unicode);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readString(0);
        
        $this->assertEquals($unicode, $value);
    }
    
    public function testStringWithNullBytes(): void
    {
        $writer = new WriteBuffer();
        $str = "Hello\x00World";
        $writer->writeString(0, $str);
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readString(0);
        
        $this->assertEquals($str, $value);
    }
}

