<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{ReadBuffer, WriteBuffer};
use FBE\Exceptions\BufferOverflowException;
use PHPUnit\Framework\TestCase;

class ReadBufferTest extends TestCase
{
    public function testConstruction(): void
    {
        $data = "\x01\x02\x03\x04";
        $buffer = new ReadBuffer($data);

        $this->assertEquals(4, $buffer->getSize());
        $this->assertEquals(0, $buffer->getOffset());
        $this->assertFalse($buffer->isEmpty());
    }

    public function testReadInt32(): void
    {
        // Little-endian: 78 56 34 12 = 0x12345678
        $data = hex2bin('78563412');
        $buffer = new ReadBuffer($data);

        $value = $buffer->readInt32(0);
        $this->assertEquals(0x12345678, $value);
    }

    public function testReadUInt32(): void
    {
        // Little-endian: EF BE AD DE = 0xDEADBEEF
        $data = hex2bin('efbeadde');
        $buffer = new ReadBuffer($data);

        $value = $buffer->readUInt32(0);
        $this->assertEquals(0xDEADBEEF, $value);
    }

    public function testReadInt64(): void
    {
        // Little-endian
        $data = hex2bin('efcdab8967452301');
        $buffer = new ReadBuffer($data);

        $value = $buffer->readInt64(0);
        $this->assertEquals(0x0123456789ABCDEF, $value);
    }

    public function testReadFloat(): void
    {
        // Create known float value
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(4);
        $writeBuffer->writeFloat(0, 3.14);

        $readBuffer = new ReadBuffer($writeBuffer->data());
        $value = $readBuffer->readFloat(0);

        $this->assertEqualsWithDelta(3.14, $value, 0.01);
    }

    public function testReadDouble(): void
    {
        // Create known double value
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(8);
        $writeBuffer->writeDouble(0, 3.14159265359);

        $readBuffer = new ReadBuffer($writeBuffer->data());
        $value = $readBuffer->readDouble(0);

        $this->assertEqualsWithDelta(3.14159265359, $value, 0.00000001);
    }

    public function testReadBool(): void
    {
        $data = "\x01\x00";
        $buffer = new ReadBuffer($data);

        $this->assertTrue($buffer->readBool(0));
        $this->assertFalse($buffer->readBool(1));
    }

    public function testReadStringInline(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);
        $writeBuffer->writeStringInline(0, 'Hello');

        $readBuffer = new ReadBuffer($writeBuffer->data());
        [$value, $consumed] = $readBuffer->readStringInline(0);

        $this->assertEquals('Hello', $value);
        $this->assertEquals(9, $consumed); // 4 bytes size + 5 bytes data
    }

    public function testReadStringPointer(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);
        $pointer = $writeBuffer->writeStringPointer(0, 'World');

        $readBuffer = new ReadBuffer($writeBuffer->data());
        $value = $readBuffer->readStringPointer(0);

        $this->assertEquals('World', $value);
    }

    public function testReadBytesInline(): void
    {
        $binaryData = "\x01\x02\x03\x04\x05";

        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);
        $writeBuffer->writeBytesInline(0, $binaryData);

        $readBuffer = new ReadBuffer($writeBuffer->data());
        [$value, $consumed] = $readBuffer->readBytesInline(0);

        $this->assertEquals($binaryData, $value);
        $this->assertEquals(9, $consumed); // 4 bytes size + 5 bytes data
    }

    public function testReadTimestamp(): void
    {
        $nanos = 1234567890123456789;

        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(8);
        $writeBuffer->writeTimestamp(0, $nanos);

        $readBuffer = new ReadBuffer($writeBuffer->data());
        $value = $readBuffer->readTimestamp(0);

        $this->assertEquals($nanos, $value);
    }

    public function testRoundTripMultipleValues(): void
    {
        // Write
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $offset = 0;
        $writeBuffer->writeInt32($offset, 12345);
        $offset += 4;

        $offset += $writeBuffer->writeStringInline($offset, 'TestString');

        $writeBuffer->writeDouble($offset, 99.99);
        $offset += 8;

        $writeBuffer->writeBool($offset, true);

        // Read
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $offset = 0;
        $int32Value = $readBuffer->readInt32($offset);
        $offset += 4;

        [$stringValue, $consumed] = $readBuffer->readStringInline($offset);
        $offset += $consumed;

        $doubleValue = $readBuffer->readDouble($offset);
        $offset += 8;

        $boolValue = $readBuffer->readBool($offset);

        // Verify
        $this->assertEquals(12345, $int32Value);
        $this->assertEquals('TestString', $stringValue);
        $this->assertEqualsWithDelta(99.99, $doubleValue, 0.0001);
        $this->assertTrue($boolValue);
    }

    public function testBoundsCheckingThrowsOnOverflow(): void
    {
        $this->expectException(BufferOverflowException::class);

        $data = "\x01\x02\x03\x04"; // Only 4 bytes
        $buffer = new ReadBuffer($data);

        // Try to read 8 bytes at offset 0
        $buffer->readInt64(0); // Should throw
    }

    public function testBoundsCheckingThrowsOnInvalidOffset(): void
    {
        $this->expectException(BufferOverflowException::class);

        $data = "\x01\x02\x03\x04"; // Only 4 bytes
        $buffer = new ReadBuffer($data);

        // Try to read at offset that exceeds buffer
        $buffer->readInt32(10); // Should throw
    }

    public function testHasValue(): void
    {
        $data = "\x01\x00\x01";
        $buffer = new ReadBuffer($data);

        $this->assertTrue($buffer->hasValue(0));
        $this->assertFalse($buffer->hasValue(1));
        $this->assertTrue($buffer->hasValue(2));
    }

    public function testEmptyStringInline(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(4);
        $writeBuffer->writeStringInline(0, '');

        $readBuffer = new ReadBuffer($writeBuffer->data());
        [$value, $consumed] = $readBuffer->readStringInline(0);

        $this->assertEquals('', $value);
        $this->assertEquals(4, $consumed); // Just size prefix
    }

    public function testNullPointerString(): void
    {
        // Pointer = 0 means null/empty
        $data = "\x00\x00\x00\x00";
        $buffer = new ReadBuffer($data);

        $value = $buffer->readStringPointer(0);
        $this->assertEquals('', $value);
    }

    /**
     * SECURITY TEST: Malicious size should not cause crash
     */
    public function testMaliciousSizeThrows(): void
    {
        $this->expectException(BufferOverflowException::class);

        // Size says 1 million bytes, but buffer only has 4
        $data = pack('V', 1_000_000) . "\x00\x00\x00\x00";
        $buffer = new ReadBuffer($data);

        $buffer->readStringInline(0); // Should throw, not crash!
    }

    /**
     * PERFORMANCE TEST
     */
    public function testPerformance(): void
    {
        // Create test data
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);
        $writeBuffer->writeInt32(0, 12345);
        $writeBuffer->writeStringInline(4, "Performance test string");
        $writeBuffer->writeDouble(50, 99.99);
        $data = $writeBuffer->data();

        $iterations = 1000;

        $start = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            $readBuffer = new ReadBuffer($data);
            $int32 = $readBuffer->readInt32(0);
            [$string, $consumed] = $readBuffer->readStringInline(4);
            $double = $readBuffer->readDouble(50);
        }
        $end = hrtime(true);

        $totalNs = $end - $start;
        $avgNs = $totalNs / $iterations;

        echo sprintf("\nV2 ReadBuffer Performance: %.2f μs/op\n", $avgNs / 1000);

        // Should be fast (< 50 μs per operation)
        $this->assertLessThan(50_000, $avgNs, "Performance regression detected");
    }
}
