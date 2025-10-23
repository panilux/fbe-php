<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class FieldModelCollectionsTest extends TestCase
{
    public function testFieldModelVector(): void
    {
        $writer = new WriteBuffer();
        $values = [1, 2, 3];
        $writer->writeVectorInt32(0, $values);
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readVectorInt32(0);
        $this->assertEquals($values, $result);
    }
}
