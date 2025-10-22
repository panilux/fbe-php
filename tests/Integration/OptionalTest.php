<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use PHPUnit\Framework\TestCase;

class OptionalTest extends TestCase
{
    public function testOptionalInt32WithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeOptionalInt32(0, 42);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalInt32(0);
        
        $this->assertEquals(42, $result);
    }

    public function testOptionalInt32Null(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeOptionalInt32(0, null);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalInt32(0);
        
        $this->assertNull($result);
    }

    public function testOptionalStringWithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(50);
        
        $writer->writeOptionalString(0, 'Panilux');
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalString(0);
        
        $this->assertEquals('Panilux', $result);
    }

    public function testOptionalStringNull(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeOptionalString(0, null);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalString(0);
        
        $this->assertNull($result);
    }

    public function testOptionalDoubleWithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(20);
        
        $writer->writeOptionalDouble(0, 3.14159);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalDouble(0);
        
        $this->assertEqualsWithDelta(3.14159, $result, 0.00001);
    }

    public function testOptionalDoubleNull(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeOptionalDouble(0, null);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readOptionalDouble(0);
        
        $this->assertNull($result);
    }
}

