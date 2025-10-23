<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class ListTest extends TestCase
{
    public function testBasicList(): void
    {
        $writer = new WriteBuffer();
        $list = [1, 2, 3, 4, 5];
        $writer->writeListInt32(0, $list);
        
        $reader = new ReadBuffer($writer->data());
        $readList = $reader->readListInt32(0);
        
        $this->assertEquals($list, $readList);
    }
}
