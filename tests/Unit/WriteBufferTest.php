<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\WriteBuffer;
use PHPUnit\Framework\TestCase;

class WriteBufferTest extends TestCase
{
    public function testWriteInt32(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeInt32(0, 0x12345678);
        
        $this->assertEquals('78563412', bin2hex($buffer->data()));
    }

    public function testWriteString(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);
        $buffer->writeString(0, 'Hello');
        
        $expected = '05000000' . bin2hex('Hello');
        $this->assertEquals($expected, bin2hex(substr($buffer->data(), 0, 9)));
    }

    public function testWriteFloat(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeFloat(0, 3.14);
        
        $this->assertNotEmpty($buffer->data());
        $this->assertEquals(4, strlen($buffer->data()));
    }

    public function testWriteDouble(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(8);
        $buffer->writeDouble(0, 3.14159);
        
        $this->assertEquals(8, strlen($buffer->data()));
    }

    public function testWriteBool(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(2);
        $buffer->writeBool(0, true);
        $buffer->writeBool(1, false);
        
        $this->assertEquals('0100', bin2hex($buffer->data()));
    }
}

