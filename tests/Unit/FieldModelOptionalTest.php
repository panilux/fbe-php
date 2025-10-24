<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{FieldModelOptionalInt32 as StdOptionalInt32, FieldModelOptionalString as StdOptionalString};
use FBE\Final\{FieldModelOptionalInt32 as FinalOptionalInt32, FieldModelOptionalString as FinalOptionalString};
use PHPUnit\Framework\TestCase;

class FieldModelOptionalTest extends TestCase
{
    public function testStandardOptionalInt32WithValue(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $optional = new StdOptionalInt32($writeBuffer, 0);
        $optional->set(42);

        $this->assertTrue($optional->hasValue());
        $this->assertEquals(5, $optional->size()); // 1 + 4

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new StdOptionalInt32($readBuffer, 0);

        $this->assertTrue($readOptional->hasValue());
        $this->assertEquals(42, $readOptional->get());
    }

    public function testStandardOptionalInt32Null(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $optional = new StdOptionalInt32($writeBuffer, 0);
        $optional->set(null);

        $this->assertFalse($optional->hasValue());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new StdOptionalInt32($readBuffer, 0);

        $this->assertFalse($readOptional->hasValue());
        $this->assertNull($readOptional->get());
    }

    public function testStandardOptionalString(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $optional = new StdOptionalString($writeBuffer, 0);
        $optional->set('Hello Optional');

        $this->assertTrue($optional->hasValue());
        $this->assertGreaterThan(0, $optional->extra()); // String data

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new StdOptionalString($readBuffer, 0);

        $this->assertTrue($readOptional->hasValue());
        $this->assertEquals('Hello Optional', $readOptional->get());
    }

    public function testFinalOptionalInt32WithValue(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $optional = new FinalOptionalInt32($writeBuffer, 0);
        $optional->set(99);

        $this->assertTrue($optional->hasValue());
        $this->assertEquals(5, $optional->size()); // 1 + 4
        $this->assertEquals(0, $optional->extra()); // Inline

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new FinalOptionalInt32($readBuffer, 0);

        $this->assertTrue($readOptional->hasValue());
        $this->assertEquals(99, $readOptional->get());
    }

    public function testFinalOptionalInt32Null(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $optional = new FinalOptionalInt32($writeBuffer, 0);
        $optional->set(null);

        $this->assertFalse($optional->hasValue());
        $this->assertEquals(5, $optional->size()); // Space still reserved

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new FinalOptionalInt32($readBuffer, 0);

        $this->assertFalse($readOptional->hasValue());
        $this->assertNull($readOptional->get());
    }

    public function testFinalOptionalStringWithValue(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $optional = new FinalOptionalString($writeBuffer, 0);
        $optional->set('Final');

        $this->assertTrue($optional->hasValue());
        $this->assertEquals(10, $optional->size()); // 1 + 4 + 5
        $this->assertEquals(0, $optional->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new FinalOptionalString($readBuffer, 0);

        $this->assertTrue($readOptional->hasValue());
        $this->assertEquals('Final', $readOptional->get());
        $this->assertEquals(10, $readOptional->size());
    }

    public function testFinalOptionalStringNull(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $optional = new FinalOptionalString($writeBuffer, 0);
        $optional->set(null);

        $this->assertFalse($optional->hasValue());
        $this->assertEquals(1, $optional->size()); // Just flag

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new FinalOptionalString($readBuffer, 0);

        $this->assertFalse($readOptional->hasValue());
        $this->assertNull($readOptional->get());
        $this->assertEquals(1, $readOptional->size());
    }

    public function testOptionalSetToNullAfterValue(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $optional = new StdOptionalInt32($writeBuffer, 0);

        // Set value
        $optional->set(123);
        $this->assertTrue($optional->hasValue());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOptional = new StdOptionalInt32($readBuffer, 0);
        $this->assertEquals(123, $readOptional->get());

        // Now set to null (overwrite)
        $writeBuffer2 = new WriteBuffer();
        $writeBuffer2->allocate(100);
        $optional2 = new StdOptionalInt32($writeBuffer2, 0);
        $optional2->set(null);

        $readBuffer2 = new ReadBuffer($writeBuffer2->data());
        $readOptional2 = new StdOptionalInt32($readBuffer2, 0);
        $this->assertNull($readOptional2->get());
    }
}
