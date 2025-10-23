<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class FloatCollectionsTest extends TestCase
{
    public function testFloatVector(): void
    {
        $writer = new WriteBuffer();
        $floats = [1.5, 2.5, 3.5];
        $writer->writeVectorFloat(0, $floats);
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorFloat(0);
        $this->assertEquals($floats, $result);
    }
}
