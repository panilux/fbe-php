<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use PHPUnit\Framework\TestCase;

class CollectionsTest extends TestCase
{
    public function testVectorInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $values = [10, 20, 30, 40, 50];
        $writer->writeVectorInt32(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorInt32(0);
        
        $this->assertEquals($values, $result);
    }

    public function testArrayInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $values = [100, 200, 300];
        $writer->writeArrayInt32(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readArrayInt32(0, 3);
        
        $this->assertEquals($values, $result);
    }

    public function testMapInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $map = [1 => 100, 2 => 200, 3 => 300];
        $writer->writeMapInt32(0, $map);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readMapInt32(0);
        
        $this->assertEquals($map, $result);
    }

    public function testSetInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $set = [10, 20, 30];
        $writer->writeSetInt32(0, $set);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readSetInt32(0);
        
        $this->assertEquals($set, $result);
    }

    public function testVectorString(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(200);
        
        $values = ['Hello', 'Panilux', 'FBE'];
        $writer->writeVectorString(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorString(0);
        
        $this->assertEquals($values, $result);
    }

    public function testVectorFloat(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $values = [1.1, 2.2, 3.3];
        $writer->writeVectorFloat(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorFloat(0);
        
        $this->assertCount(3, $result);
        $this->assertEqualsWithDelta(1.1, $result[0], 0.01);
        $this->assertEqualsWithDelta(2.2, $result[1], 0.01);
        $this->assertEqualsWithDelta(3.3, $result[2], 0.01);
    }
}

