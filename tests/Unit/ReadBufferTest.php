<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\ReadBuffer;
use PHPUnit\Framework\TestCase;

class ReadBufferTest extends TestCase
{
    public function testReadInt32(): void
    {
        $binary = hex2bin('78563412');
        $buffer = new ReadBuffer($binary);
        
        $this->assertEquals(0x12345678, $buffer->readInt32(0));
    }

    public function testReadString(): void
    {
        $binary = hex2bin('05000000') . 'Hello';
        $buffer = new ReadBuffer($binary);
        
        $this->assertEquals('Hello', $buffer->readString(0));
    }

    public function testReadFloat(): void
    {
        $writer = new \FBE\WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, 3.14);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEqualsWithDelta(3.14, $reader->readFloat(0), 0.01);
    }

    public function testReadDouble(): void
    {
        $writer = new \FBE\WriteBuffer();
        $writer->allocate(8);
        $writer->writeDouble(0, 3.14159);
        
        $reader = new ReadBuffer($writer->data());
        $this->assertEqualsWithDelta(3.14159, $reader->readDouble(0), 0.00001);
    }

    public function testReadBool(): void
    {
        $binary = hex2bin('0100');
        $buffer = new ReadBuffer($binary);
        
        $this->assertTrue($buffer->readBool(0));
        $this->assertFalse($buffer->readBool(1));
    }
}

