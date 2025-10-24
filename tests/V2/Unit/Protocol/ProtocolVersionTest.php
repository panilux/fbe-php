<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit\Protocol;

use FBE\V2\Protocol\ProtocolVersion;
use PHPUnit\Framework\TestCase;

class ProtocolVersionTest extends TestCase
{
    public function testConstruction(): void
    {
        $version = new ProtocolVersion(1, 2, 3);

        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testCurrent(): void
    {
        $version = ProtocolVersion::current();

        $this->assertEquals(ProtocolVersion::CURRENT_MAJOR, $version->major);
        $this->assertEquals(ProtocolVersion::CURRENT_MINOR, $version->minor);
        $this->assertEquals(ProtocolVersion::CURRENT_PATCH, $version->patch);
    }

    public function testParse(): void
    {
        $version = ProtocolVersion::parse('1.2.3');

        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testParseInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid version format');

        ProtocolVersion::parse('1.2'); // Missing patch
    }

    public function testToString(): void
    {
        $version = new ProtocolVersion(1, 2, 3);

        $this->assertEquals('1.2.3', $version->toString());
        $this->assertEquals('1.2.3', (string)$version);
    }

    public function testIsCompatibleSameVersion(): void
    {
        $v1 = new ProtocolVersion(1, 0, 0);
        $v2 = new ProtocolVersion(1, 0, 0);

        $this->assertTrue($v1->isCompatible($v2));
        $this->assertTrue($v2->isCompatible($v1));
    }

    public function testIsCompatibleNewerMinor(): void
    {
        $v1 = new ProtocolVersion(1, 2, 0);
        $v2 = new ProtocolVersion(1, 1, 0);

        // v1.2 is compatible with v1.1 (backward compatible)
        $this->assertTrue($v1->isCompatible($v2));

        // v1.1 is NOT compatible with v1.2 (needs upgrade)
        $this->assertFalse($v2->isCompatible($v1));
    }

    public function testIsCompatibleDifferentMajor(): void
    {
        $v1 = new ProtocolVersion(1, 0, 0);
        $v2 = new ProtocolVersion(2, 0, 0);

        // Different major versions are incompatible
        $this->assertFalse($v1->isCompatible($v2));
        $this->assertFalse($v2->isCompatible($v1));
    }

    public function testIsCompatibleNewerPatch(): void
    {
        $v1 = new ProtocolVersion(1, 0, 2);
        $v2 = new ProtocolVersion(1, 0, 1);

        $this->assertTrue($v1->isCompatible($v2));
        $this->assertFalse($v2->isCompatible($v1));
    }

    public function testCompare(): void
    {
        $v1_0_0 = new ProtocolVersion(1, 0, 0);
        $v1_1_0 = new ProtocolVersion(1, 1, 0);
        $v2_0_0 = new ProtocolVersion(2, 0, 0);

        $this->assertEquals(0, $v1_0_0->compare($v1_0_0));
        $this->assertEquals(-1, $v1_0_0->compare($v1_1_0));
        $this->assertEquals(1, $v1_1_0->compare($v1_0_0));
        $this->assertEquals(-1, $v1_0_0->compare($v2_0_0));
    }

    public function testNegativeVersionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be non-negative');

        new ProtocolVersion(-1, 0, 0);
    }
}
