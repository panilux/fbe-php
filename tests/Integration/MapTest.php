<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class MapTest extends TestCase
{
    public function testBasicMap(): void
    {
        $writer = new WriteBuffer();
        $map = ["key1" => 100, "key2" => 200];
        $writer->writeMapStringInt32(0, $map);
        
        $reader = new ReadBuffer($writer->data());
        $readMap = $reader->readMapStringInt32(0);
        
        $this->assertEquals($map, $readMap);
    }
    
    public function testEmptyMap(): void
    {
        $writer = new WriteBuffer();
        $map = [];
        $writer->writeMapStringInt32(0, $map);
        
        $reader = new ReadBuffer($writer->data());
        $readMap = $reader->readMapStringInt32(0);
        
        $this->assertEquals($map, $readMap);
    }
}
