<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{ReadBuffer, WriteBuffer};
use PHPUnit\Framework\TestCase;

/**
 * Tests for all primitive types (int8-64, uint8-64, char, wchar)
 */
class BufferPrimitivesTest extends TestCase
{
    public function testInt8(): void
    {
        $buffer = new WriteBuffer(10);
        $buffer->writeInt8(0, -128);
        $buffer->writeInt8(1, 0);
        $buffer->writeInt8(2, 127);

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(-128, $readBuffer->readInt8(0));
        $this->assertEquals(0, $readBuffer->readInt8(1));
        $this->assertEquals(127, $readBuffer->readInt8(2));
    }

    public function testUInt8(): void
    {
        $buffer = new WriteBuffer(10);
        $buffer->writeUInt8(0, 0);
        $buffer->writeUInt8(1, 128);
        $buffer->writeUInt8(2, 255);

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(0, $readBuffer->readUInt8(0));
        $this->assertEquals(128, $readBuffer->readUInt8(1));
        $this->assertEquals(255, $readBuffer->readUInt8(2));
    }

    public function testInt16(): void
    {
        $buffer = new WriteBuffer(20);
        $buffer->writeInt16(0, -32768);
        $buffer->writeInt16(2, 0);
        $buffer->writeInt16(4, 32767);

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(-32768, $readBuffer->readInt16(0));
        $this->assertEquals(0, $readBuffer->readInt16(2));
        $this->assertEquals(32767, $readBuffer->readInt16(4));
    }

    public function testUInt16(): void
    {
        $buffer = new WriteBuffer(20);
        $buffer->writeUInt16(0, 0);
        $buffer->writeUInt16(2, 32768);
        $buffer->writeUInt16(4, 65535);

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(0, $readBuffer->readUInt16(0));
        $this->assertEquals(32768, $readBuffer->readUInt16(2));
        $this->assertEquals(65535, $readBuffer->readUInt16(4));
    }

    public function testUInt32(): void
    {
        $buffer = new WriteBuffer(20);
        $buffer->writeUInt32(0, 0);
        $buffer->writeUInt32(4, 2147483648); // 2^31
        $buffer->writeUInt32(8, 4294967295); // 2^32-1

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(0, $readBuffer->readUInt32(0));
        $this->assertEquals(2147483648, $readBuffer->readUInt32(4));
        $this->assertEquals(4294967295, $readBuffer->readUInt32(8));
    }

    public function testUInt64(): void
    {
        $buffer = new WriteBuffer(30);
        $buffer->writeUInt64(0, 0);
        $buffer->writeUInt64(8, 1234567890123); // Safe uint64 value
        $buffer->writeUInt64(16, PHP_INT_MAX); // Max safe value

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(0, $readBuffer->readUInt64(0));
        $this->assertEquals(1234567890123, $readBuffer->readUInt64(8));
        $this->assertEquals(PHP_INT_MAX, $readBuffer->readUInt64(16));
    }

    public function testChar(): void
    {
        $buffer = new WriteBuffer(10);
        $buffer->writeChar(0, 65);  // 'A'
        $buffer->writeChar(1, 66);  // 'B'
        $buffer->writeChar(2, 255); // Max char

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(65, $readBuffer->readChar(0));
        $this->assertEquals(66, $readBuffer->readChar(1));
        $this->assertEquals(255, $readBuffer->readChar(2));
    }

    public function testWChar(): void
    {
        $buffer = new WriteBuffer(20);
        $buffer->writeWChar(0, 0x0041);     // 'A' in Unicode
        $buffer->writeWChar(4, 0x4E2D);     // 中 (Chinese character)
        $buffer->writeWChar(8, 0x1F600);    // 😀 (Emoji)

        $readBuffer = new ReadBuffer($buffer->data());
        $this->assertEquals(0x0041, $readBuffer->readWChar(0));
        $this->assertEquals(0x4E2D, $readBuffer->readWChar(4));
        $this->assertEquals(0x1F600, $readBuffer->readWChar(8));
    }

    public function testAllPrimitivesSequential(): void
    {
        $buffer = new WriteBuffer(100);
        $offset = 0;

        // Write all primitive types
        $buffer->writeBool($offset, true); $offset += 1;
        $buffer->writeInt8($offset, -42); $offset += 1;
        $buffer->writeUInt8($offset, 200); $offset += 1;
        $buffer->writeChar($offset, 88); $offset += 1;
        $buffer->writeInt16($offset, -1000); $offset += 2;
        $buffer->writeUInt16($offset, 50000); $offset += 2;
        $buffer->writeInt32($offset, -100000); $offset += 4;
        $buffer->writeUInt32($offset, 3000000000); $offset += 4;
        $buffer->writeWChar($offset, 0x263A); $offset += 4; // ☺
        $buffer->writeInt64($offset, -9876543210); $offset += 8;
        $buffer->writeUInt64($offset, 1234567890123); $offset += 8;
        $buffer->writeFloat($offset, 3.14159); $offset += 4;
        $buffer->writeDouble($offset, 2.718281828459); $offset += 8;

        // Read back and verify
        $readBuffer = new ReadBuffer($buffer->data());
        $offset = 0;

        $this->assertTrue($readBuffer->readBool($offset)); $offset += 1;
        $this->assertEquals(-42, $readBuffer->readInt8($offset)); $offset += 1;
        $this->assertEquals(200, $readBuffer->readUInt8($offset)); $offset += 1;
        $this->assertEquals(88, $readBuffer->readChar($offset)); $offset += 1;
        $this->assertEquals(-1000, $readBuffer->readInt16($offset)); $offset += 2;
        $this->assertEquals(50000, $readBuffer->readUInt16($offset)); $offset += 2;
        $this->assertEquals(-100000, $readBuffer->readInt32($offset)); $offset += 4;
        $this->assertEquals(3000000000, $readBuffer->readUInt32($offset)); $offset += 4;
        $this->assertEquals(0x263A, $readBuffer->readWChar($offset)); $offset += 4;
        $this->assertEquals(-9876543210, $readBuffer->readInt64($offset)); $offset += 8;
        $this->assertEquals(1234567890123, $readBuffer->readUInt64($offset)); $offset += 8;
        $this->assertEqualsWithDelta(3.14159, $readBuffer->readFloat($offset), 0.00001); $offset += 4;
        $this->assertEqualsWithDelta(2.718281828459, $readBuffer->readDouble($offset), 0.0000000001);
    }
}
