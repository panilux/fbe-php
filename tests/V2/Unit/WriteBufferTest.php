<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Common\WriteBuffer;
use FBE\V2\Exceptions\{BufferOverflowException, BufferUnderflowException};
use PHPUnit\Framework\TestCase;

class WriteBufferTest extends TestCase
{
    public function testConstruction(): void
    {
        $buffer = new WriteBuffer();
        $this->assertEquals(0, $buffer->getSize());
        $this->assertEquals(0, $buffer->getOffset());
        $this->assertGreaterThan(0, $buffer->capacity());
        $this->assertTrue($buffer->isEmpty());
    }

    public function testWriteInt32(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeInt32(0, 0x12345678);

        // Little-endian: 78 56 34 12
        $this->assertEquals('78563412', bin2hex($buffer->data()));
    }

    public function testWriteUInt32(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeUInt32(0, 0xDEADBEEF);

        // Little-endian: EF BE AD DE
        $this->assertEquals('efbeadde', bin2hex($buffer->data()));
    }

    public function testWriteInt64(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(8);
        $buffer->writeInt64(0, 0x0123456789ABCDEF);

        $expected = 'efcdab8967452301'; // Little-endian
        $this->assertEquals($expected, bin2hex($buffer->data()));
    }

    public function testWriteFloat(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeFloat(0, 3.14);

        $this->assertEquals(4, strlen($buffer->data()));

        // Verify by reading back
        $readValue = unpack('f', $buffer->data())[1];
        $this->assertEqualsWithDelta(3.14, $readValue, 0.01);
    }

    public function testWriteDouble(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(8);
        $buffer->writeDouble(0, 3.14159265359);

        $this->assertEquals(8, strlen($buffer->data()));

        // Verify by reading back
        $readValue = unpack('d', $buffer->data())[1];
        $this->assertEqualsWithDelta(3.14159265359, $readValue, 0.00000001);
    }

    public function testWriteBool(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(2);
        $buffer->writeBool(0, true);
        $buffer->writeBool(1, false);

        $this->assertEquals('0100', bin2hex($buffer->data()));
    }

    public function testWriteStringInline(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $bytesWritten = $buffer->writeStringInline(0, 'Hello');

        $this->assertEquals(9, $bytesWritten); // 4 bytes size + 5 bytes data
        $this->assertEquals('05000000' . bin2hex('Hello'), bin2hex(substr($buffer->data(), 0, 9)));
    }

    public function testWriteStringPointer(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        // Write pointer at offset 0
        $pointer = $buffer->writeStringPointer(0, 'World');

        $this->assertGreaterThan(0, $pointer);

        // Verify pointer value at offset 0
        $pointerValue = unpack('V', substr($buffer->data(), 0, 4))[1];
        $this->assertEquals($pointer, $pointerValue);

        // Verify string at pointer location
        $size = unpack('V', substr($buffer->data(), $pointer, 4))[1];
        $this->assertEquals(5, $size);

        $string = substr($buffer->data(), $pointer + 4, $size);
        $this->assertEquals('World', $string);
    }

    public function testWriteMultipleValues(): void
    {
        $buffer = new WriteBuffer();

        $offset = 0;

        // Write int32
        $buffer->allocate(4);
        $buffer->writeInt32($offset, 12345);
        $offset += 4;

        // Write string inline (4 + 4 bytes)
        $buffer->allocate(8);
        $offset += $buffer->writeStringInline($offset, 'Test');

        // Write double
        $buffer->allocate(8);
        $buffer->writeDouble($offset, 99.99);
        $offset += 8;

        $this->assertGreaterThan(0, $buffer->getSize());
        $this->assertEquals($offset, $buffer->getSize());
    }

    public function testBufferGrowth(): void
    {
        $buffer = new WriteBuffer(16); // Small initial capacity

        // Write data that exceeds initial capacity
        for ($i = 0; $i < 100; $i++) {
            $buffer->writeInt32($i * 4, $i);
        }

        $this->assertGreaterThanOrEqual(400, $buffer->capacity());
        $this->assertEquals(400, $buffer->getSize());
    }

    public function testNegativeOffsetThrows(): void
    {
        $this->expectException(BufferUnderflowException::class);

        $buffer = new WriteBuffer();
        $buffer->writeInt32(-1, 123);
    }

    public function testAllocate(): void
    {
        $buffer = new WriteBuffer();

        $offset1 = $buffer->allocate(10);
        $this->assertEquals(0, $offset1);
        $this->assertEquals(10, $buffer->getSize());

        $offset2 = $buffer->allocate(20);
        $this->assertEquals(10, $offset2);
        $this->assertEquals(30, $buffer->getSize());
    }

    public function testClear(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);
        $buffer->writeInt32(0, 123);

        $this->assertGreaterThan(0, $buffer->getSize());

        $buffer->clear();

        $this->assertEquals(0, $buffer->getSize());
        $this->assertEquals(0, $buffer->getOffset());
        $this->assertTrue($buffer->isEmpty());
    }

    public function testReset(): void
    {
        $buffer = new WriteBuffer();
        $buffer->shift(10);

        $this->assertEquals(10, $buffer->getOffset());

        $buffer->reset();

        $this->assertEquals(0, $buffer->getOffset());
    }

    public function testWriteBytesInline(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $binaryData = "\x01\x02\x03\x04\x05";
        $bytesWritten = $buffer->writeBytesInline(0, $binaryData);

        $this->assertEquals(9, $bytesWritten); // 4 bytes size + 5 bytes data

        // Verify size prefix
        $size = unpack('V', substr($buffer->data(), 0, 4))[1];
        $this->assertEquals(5, $size);

        // Verify data
        $data = substr($buffer->data(), 4, 5);
        $this->assertEquals($binaryData, $data);
    }

    public function testWriteTimestamp(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(8);

        $nanos = 1234567890123456789;
        $buffer->writeTimestamp(0, $nanos);

        $this->assertEquals(8, strlen($buffer->data()));

        // Verify by reading back
        $readValue = unpack('P', $buffer->data())[1];
        $this->assertEquals($nanos, $readValue);
    }

    /**
     * PERFORMANCE TEST: Compare v2 vs v1 speed
     */
    public function testPerformance(): void
    {
        $iterations = 1000;

        $start = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $buffer = new WriteBuffer();
            $buffer->allocate(100);
            $buffer->writeInt32(0, $i);
            $buffer->writeStringInline(4, "Test string $i");
            $buffer->writeDouble(50, $i * 1.5);
            $data = $buffer->data();
        }
        $end = hrtime(true);

        $totalNs = $end - $start;
        $avgNs = $totalNs / $iterations;

        echo sprintf("\nV2 WriteBuffer Performance: %.2f μs/op\n", $avgNs / 1000);

        // Should be reasonably fast (< 100 μs per operation)
        $this->assertLessThan(100_000, $avgNs, "Performance regression detected");
    }
}
