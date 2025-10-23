<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class StringCollectionsTest extends TestCase
{
    public function testStringVector(): void
    {
        $writer = new WriteBuffer();
        $strings = ["hello", "world"];
        $writer->writeVectorString(0, $strings);
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorString(0);
        $this->assertEquals($strings, $result);
    }
}
