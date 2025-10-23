<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class BaseTypesTest extends TestCase
{
    public function testBoolTrue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeBool(0, true);
        $reader = new ReadBuffer($writer->data());
        $this->assertTrue($reader->readBool(0));
    }
    
    public function testInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeInt32(0, 2147483647);
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals(2147483647, $reader->readInt32(0));
    }
    
    public function testFloat(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeFloat(0, 3.14159);
        $reader = new ReadBuffer($writer->data());
        $this->assertEqualsWithDelta(3.14159, $reader->readFloat(0), 0.00001);
    }
    
    public function testString(): void
    {
        $writer = new WriteBuffer();
        $writer->writeString(0, 'Hello');
        $reader = new ReadBuffer($writer->data());
        $this->assertEquals('Hello', $reader->readString(0));
    }
}
