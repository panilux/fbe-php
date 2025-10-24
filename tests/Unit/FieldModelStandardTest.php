<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{FieldModelInt32, FieldModelDouble, FieldModelString, FieldModelUuid, FieldModelDecimal};
use FBE\Types\{Uuid, Decimal};
use PHPUnit\Framework\TestCase;

class FieldModelStandardTest extends TestCase
{
    public function testFieldModelInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelInt32($writeBuffer, 0);
        $this->assertEquals(4, $field->size());

        $field->set(12345);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelInt32($readBuffer, 0);

        $this->assertEquals(12345, $readField->get());
    }

    public function testFieldModelDouble(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelDouble($writeBuffer, 0);
        $this->assertEquals(8, $field->size());

        $field->set(123.456);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelDouble($readBuffer, 0);

        $this->assertEqualsWithDelta(123.456, $readField->get(), 0.0001);
    }

    /**
     * CRITICAL: Test Standard format uses POINTER for strings
     */
    public function testFieldModelStringPointer(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelString($writeBuffer, 0);
        $this->assertEquals(4, $field->size()); // Pointer only!

        $field->set('Hello Standard');

        // Verify extra size (data at pointer location)
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelString($readBuffer, 0);

        $this->assertEquals(4, $readField->size()); // Pointer size
        $this->assertGreaterThan(0, $readField->extra()); // Has extra data
        $this->assertEquals('Hello Standard', $readField->get());
    }

    public function testFieldModelUuid(): void
    {
        $uuid = Uuid::random();

        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelUuid($writeBuffer, 0);
        $this->assertEquals(16, $field->size());

        $field->set($uuid);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelUuid($readBuffer, 0);

        $readUuid = $readField->get();
        $this->assertTrue($uuid->equals($readUuid));
    }

    public function testFieldModelDecimal(): void
    {
        $decimal = Decimal::fromString('999.99');

        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelDecimal($writeBuffer, 0);
        $this->assertEquals(16, $field->size());

        $field->set($decimal);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelDecimal($readBuffer, 0);

        $readDecimal = $readField->get();
        $this->assertEquals('999.99', $readDecimal->toString());
    }

    public function testFieldModelOffset(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        // Write at offset 10
        $field = new FieldModelInt32($writeBuffer, 10);
        $field->set(42);

        // Read from offset 10
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelInt32($readBuffer, 10);

        $this->assertEquals(42, $readField->get());
    }

    public function testMultipleFields(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $offset = 0;

        // Write int32
        $field1 = new FieldModelInt32($writeBuffer, $offset);
        $field1->set(100);
        $offset += $field1->size();

        // Write double
        $field2 = new FieldModelDouble($writeBuffer, $offset);
        $field2->set(3.14);
        $offset += $field2->size();

        // Write string (pointer-based)
        $field3 = new FieldModelString($writeBuffer, $offset);
        $field3->set('Test');
        $offset += $field3->size();

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $offset = 0;

        $read1 = new FieldModelInt32($readBuffer, $offset);
        $this->assertEquals(100, $read1->get());
        $offset += $read1->size();

        $read2 = new FieldModelDouble($readBuffer, $offset);
        $this->assertEqualsWithDelta(3.14, $read2->get(), 0.01);
        $offset += $read2->size();

        $read3 = new FieldModelString($readBuffer, $offset);
        $this->assertEquals('Test', $read3->get());
    }
}
