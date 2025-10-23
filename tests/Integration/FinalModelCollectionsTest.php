<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class FinalModelCollectionsTest extends TestCase
{
    public function testFinalModelArray(): void
    {
        $writer = new WriteBuffer();
        $array = [1, 2, 3, 4, 5];
        $writer->writeArrayInt32(0, $array, 5);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readArrayInt32(0, 5);
        
        $this->assertEquals($array, $result);
    }
    
    public function testFinalModelVector(): void
    {
        $writer = new WriteBuffer();
        $vector = [10, 20, 30, 40];
        $writer->writeVectorInt32(0, $vector);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorInt32(0);
        
        $this->assertEquals($vector, $result);
    }
    
    public function testFinalModelList(): void
    {
        $writer = new WriteBuffer();
        $list = [100, 200, 300];
        $writer->writeListInt32(0, $list);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readListInt32(0);
        
        $this->assertEquals($list, $result);
    }
    
    public function testFinalModelMap(): void
    {
        $writer = new WriteBuffer();
        $map = [1 => 10, 2 => 20, 3 => 30];
        $writer->writeMapInt32(0, $map);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readMapInt32(0);
        
        $this->assertEquals($map, $result);
    }
    
    public function testFinalModelSet(): void
    {
        $writer = new WriteBuffer();
        $set = [5, 10, 15, 20];
        $writer->writeSetInt32(0, $set);
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readSetInt32(0);
        
        $this->assertEquals($set, $result);
    }
}

