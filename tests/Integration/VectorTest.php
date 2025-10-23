<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class VectorTest extends TestCase
{
    public function testBasicVector(): void
    {
        $writer = new WriteBuffer();
        $values = [10, 20, 30, 40, 50];
        $writer->writeVectorInt32(0, $values);
        
        $reader = new ReadBuffer($writer->data());
        $readValues = $reader->readVectorInt32(0);
        
        $this->assertEquals($values, $readValues);
    }
    
    public function testEmptyVector(): void
    {
        $writer = new WriteBuffer();
        $emptyValues = [];
        $writer->writeVectorInt32(0, $emptyValues);
        
        $reader = new ReadBuffer($writer->data());
        $readEmpty = $reader->readVectorInt32(0);
        
        $this->assertEquals($emptyValues, $readEmpty);
    }
    
    public function testLargeVector(): void
    {
        $writer = new WriteBuffer();
        $largeValues = range(0, 999);
        $writer->writeVectorInt32(0, $largeValues);
        
        $reader = new ReadBuffer($writer->data());
        $readLarge = $reader->readVectorInt32(0);
        
        $this->assertEquals($largeValues, $readLarge);
    }
}

