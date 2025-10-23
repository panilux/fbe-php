<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Types\Uuid;
use FBE\V2\Common\{WriteBuffer, ReadBuffer};
use PHPUnit\Framework\TestCase;

class UuidTest extends TestCase
{
    public function testConstruction(): void
    {
        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $this->assertInstanceOf(Uuid::class, $uuid);
    }

    public function testToString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid = new Uuid($uuidString);

        $this->assertEquals($uuidString, $uuid->toString());
        $this->assertEquals($uuidString, (string)$uuid);
    }

    public function testToBytes(): void
    {
        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $bytes = $uuid->toBytes();

        $this->assertEquals(16, strlen($bytes));

        // Verify big-endian byte order
        $expected = hex2bin('550e8400e29b41d4a716446655440000');
        $this->assertEquals($expected, $bytes);
    }

    public function testFromBytes(): void
    {
        $bytes = hex2bin('550e8400e29b41d4a716446655440000');
        $uuid = Uuid::fromBytes($bytes);

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $uuid->toString());
    }

    public function testRandomUuid(): void
    {
        $uuid = Uuid::random();

        $this->assertInstanceOf(Uuid::class, $uuid);
        $this->assertEquals(4, $uuid->version()); // Version 4
        $this->assertFalse($uuid->isNil());
    }

    public function testNilUuid(): void
    {
        $uuid = new Uuid('00000000-0000-0000-0000-000000000000');
        $this->assertTrue($uuid->isNil());
    }

    public function testEquals(): void
    {
        $uuid1 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid2 = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $uuid3 = new Uuid('550e8400-e29b-41d4-a716-446655440001');

        $this->assertTrue($uuid1->equals($uuid2));
        $this->assertFalse($uuid1->equals($uuid3));
    }

    public function testRoundTripWithBuffer(): void
    {
        $originalUuid = Uuid::random();

        // Write
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(16);
        $writeBuffer->writeUuid(0, $originalUuid);

        // Read
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readUuid = $readBuffer->readUuid(0);

        $this->assertTrue($originalUuid->equals($readUuid));
        $this->assertEquals($originalUuid->toString(), $readUuid->toString());
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Uuid('invalid-uuid');
    }

    public function testInvalidBytesLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Uuid::fromBytes('short');
    }

    /**
     * CRITICAL: Test big-endian byte ordering (FBE spec requirement)
     */
    public function testBigEndianByteOrder(): void
    {
        // Known UUID with specific byte pattern
        $uuid = new Uuid('12345678-9abc-def0-1234-567890abcdef');
        $bytes = $uuid->toBytes();

        // Verify first 4 bytes are big-endian
        $this->assertEquals(0x12, ord($bytes[0]));
        $this->assertEquals(0x34, ord($bytes[1]));
        $this->assertEquals(0x56, ord($bytes[2]));
        $this->assertEquals(0x78, ord($bytes[3]));

        // NOT little-endian (would be 0x78, 0x56, 0x34, 0x12)
        $this->assertNotEquals(0x78, ord($bytes[0]));
    }
}
