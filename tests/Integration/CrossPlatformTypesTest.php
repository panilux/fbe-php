<?php
declare(strict_types=1);
namespace FBE\Tests\Integration;
use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class CrossPlatformTypesTest extends TestCase
{
    public function testBasic(): void
    {
        $this->assertTrue(true);
    }
}
