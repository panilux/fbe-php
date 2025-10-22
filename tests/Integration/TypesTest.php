<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    public function testAllPrimitiveTypes(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $offset = 0;
        
        // Bool
        $writer->writeBool($offset, true);
        $offset += 1;
        
        // Byte (UInt8)
        $writer->writeUInt8($offset, 255);
        $offset += 1;
        
        // Int8
        $writer->writeInt8($offset, -128);
        $offset += 1;
        
        // UInt8
        $writer->writeUInt8($offset, 255);
        $offset += 1;
        
        // Int16
        $writer->writeInt16($offset, -32768);
        $offset += 2;
        
        // UInt16
        $writer->writeUInt16($offset, 65535);
        $offset += 2;
        
        // Int32
        $writer->writeInt32($offset, -2147483648);
        $offset += 4;
        
        // UInt32
        $writer->writeUInt32($offset, 4294967295);
        $offset += 4;
        
        // Int64
        $writer->writeInt64($offset, -9223372036854775807);
        $offset += 8;
        
        // Float
        $writer->writeFloat($offset, 3.14);
        $offset += 4;
        
        // Double
        $writer->writeDouble($offset, 3.14159265359);
        $offset += 8;
        
        // Read back
        $reader = new ReadBuffer($writer->data());
        $offset = 0;
        
        $this->assertTrue($reader->readBool($offset));
        $offset += 1;
        
        $this->assertEquals(255, $reader->readUInt8($offset));
        $offset += 1;
        
        $this->assertEquals(-128, $reader->readInt8($offset));
        $offset += 1;
        
        $this->assertEquals(255, $reader->readUInt8($offset));
        $offset += 1;
        
        $this->assertEquals(-32768, $reader->readInt16($offset));
        $offset += 2;
        
        $this->assertEquals(65535, $reader->readUInt16($offset));
        $offset += 2;
        
        $this->assertEquals(-2147483648, $reader->readInt32($offset));
        $offset += 4;
        
        $this->assertEquals(4294967295, $reader->readUInt32($offset));
        $offset += 4;
        
        $this->assertEquals(-9223372036854775807, $reader->readInt64($offset));
        $offset += 8;
        
        $this->assertEqualsWithDelta(3.14, $reader->readFloat($offset), 0.01);
        $offset += 4;
        
        $this->assertEqualsWithDelta(3.14159265359, $reader->readDouble($offset), 0.00000001);
    }

    public function testStringType(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $testStrings = ['Hello', 'Panilux', ''];
        $offset = 0;
        
        foreach ($testStrings as $str) {
            $writer->writeString($offset, $str);
            $offset += 4 + strlen($str);
        }
        
        $reader = new ReadBuffer($writer->data());
        $offset = 0;
        
        foreach ($testStrings as $str) {
            $this->assertEquals($str, $reader->readString($offset));
            $offset += 4 + strlen($str);
        }
    }
}

