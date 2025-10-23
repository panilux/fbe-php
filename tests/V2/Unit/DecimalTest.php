<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Types\Decimal;
use FBE\V2\Common\{WriteBuffer, ReadBuffer};
use PHPUnit\Framework\TestCase;

class DecimalTest extends TestCase
{
    public function testConstruction(): void
    {
        $decimal = new Decimal(12345, 2, false);
        $this->assertInstanceOf(Decimal::class, $decimal);
    }

    public function testFromFloat(): void
    {
        $decimal = Decimal::fromFloat(123.45);

        $this->assertEqualsWithDelta(123.45, $decimal->toFloat(), 0.0001);
    }

    public function testFromString(): void
    {
        $decimal = Decimal::fromString('123.45');

        $this->assertEquals('123.45', $decimal->toString());
        $this->assertEqualsWithDelta(123.45, $decimal->toFloat(), 0.0001);
    }

    public function testNegativeNumber(): void
    {
        $decimal = Decimal::fromString('-99.99');

        $this->assertTrue($decimal->isNegative());
        $this->assertEquals('-99.99', $decimal->toString());
        $this->assertEqualsWithDelta(-99.99, $decimal->toFloat(), 0.0001);
    }

    public function testHighPrecision(): void
    {
        // Test decimal with many decimal places
        $decimal = Decimal::fromString('123.456789012345678901234567');

        $this->assertGreaterThan(10, $decimal->getScale());
        $this->assertStringContainsString('123.4567', $decimal->toString());
    }

    public function testToBytes(): void
    {
        $decimal = Decimal::fromString('123.45');
        $bytes = $decimal->toBytes();

        $this->assertEquals(16, strlen($bytes));

        // Byte 14 should contain scale
        $this->assertEquals(2, ord($bytes[14]));

        // Byte 15 should contain sign (positive = 0x00)
        $this->assertEquals(0x00, ord($bytes[15]));
    }

    public function testFromBytes(): void
    {
        $original = Decimal::fromString('999.99');
        $bytes = $original->toBytes();

        $restored = Decimal::fromBytes($bytes);

        $this->assertEquals($original->toString(), $restored->toString());
        $this->assertEqualsWithDelta($original->toFloat(), $restored->toFloat(), 0.0001);
    }

    public function testRoundTripWithBuffer(): void
    {
        $original = Decimal::fromString('12345.6789');

        // Write
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(16);
        $writeBuffer->writeDecimal(0, $original);

        // Read
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $restored = $readBuffer->readDecimal(0);

        $this->assertEquals($original->toString(), $restored->toString());
        $this->assertEqualsWithDelta($original->toFloat(), $restored->toFloat(), 0.0001);
    }

    public function testZero(): void
    {
        $decimal = Decimal::fromString('0.0');

        $this->assertEquals('0', $decimal->toString());
        $this->assertEquals(0.0, $decimal->toFloat());
    }

    public function testEquals(): void
    {
        $decimal1 = Decimal::fromString('123.45');
        $decimal2 = Decimal::fromString('123.45');
        $decimal3 = Decimal::fromString('123.46');

        $this->assertTrue($decimal1->equals($decimal2));
        $this->assertFalse($decimal1->equals($decimal3));
    }

    public function testScaleValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Decimal(123, 30, false); // Scale > 28
    }

    public function testInvalidBytesLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Decimal::fromBytes('short');
    }

    /**
     * CRITICAL: Test 96-bit precision (not 64-bit!)
     */
    public function testFullPrecision(): void
    {
        // Create decimal with value larger than 64-bit
        // Max uint64: 18,446,744,073,709,551,615
        // Let's use a larger value
        $largeValue = '99999999999999999999999999'; // 26 digits

        $decimal = Decimal::fromString($largeValue);

        // Should not lose precision
        $restored = $decimal->toString();
        $this->assertEquals($largeValue, $restored);
    }

    /**
     * Test negative decimal bytes
     */
    public function testNegativeBytes(): void
    {
        $decimal = Decimal::fromString('-123.45');
        $bytes = $decimal->toBytes();

        // Byte 15 should be 0x80 for negative
        $this->assertEquals(0x80, ord($bytes[15]));

        // Round trip
        $restored = Decimal::fromBytes($bytes);
        $this->assertTrue($restored->isNegative());
        $this->assertEquals('-123.45', $restored->toString());
    }
}
