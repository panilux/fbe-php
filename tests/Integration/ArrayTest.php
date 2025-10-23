<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class ArrayTest extends TestCase
{
    public function testBasicArray(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(12);
        $values = [10, 20, 30];
        $writer->writeArrayInt32(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $readValues = $reader->readArrayInt32(0, 3);
        
        $this->assertEquals($values, $readValues);
    }
    
    public function testLargeArray(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(400);
        $largeValues = range(0, 99);
        $writer->writeArrayInt32(0, $largeValues);
        
        $reader = new ReadBuffer($writer->data());
        $readLarge = $reader->readArrayInt32(0, 100);
        
        $this->assertEquals($largeValues, $readLarge);
    }
    
    public function testEmptyArray(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(0);
        $values = [];
        $writer->writeArrayInt32(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $readValues = $reader->readArrayInt32(0, 0);
        
        $this->assertEquals($values, $readValues);
    }
}

