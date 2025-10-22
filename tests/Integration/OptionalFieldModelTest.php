<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\FieldModelInt32;
use FBE\FieldModelOptional;
use FBE\FieldModelString;
use FBE\FinalModelInt32;
use FBE\FinalModelOptional;
use FBE\FinalModelString;
use FBE\ReadBuffer;
use FBE\WriteBuffer;
use PHPUnit\Framework\TestCase;

final class OptionalFieldModelTest extends TestCase
{
    public function testFieldModelOptionalInt32WithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional int32 field model
        $valueModel = new FieldModelInt32($writer, 1);
        $optionalModel = new FieldModelOptional($writer, 0, $valueModel);

        // Set value
        $optionalModel->set(42);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FieldModelInt32($reader, 1);
        $optionalModelRead = new FieldModelOptional($reader, 0, $valueModelRead);

        $this->assertTrue($optionalModelRead->hasValue());
        $this->assertSame(42, $optionalModelRead->get());
    }

    public function testFieldModelOptionalInt32Null(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional int32 field model
        $valueModel = new FieldModelInt32($writer, 1);
        $optionalModel = new FieldModelOptional($writer, 0, $valueModel);

        // Set null
        $optionalModel->set(null);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FieldModelInt32($reader, 1);
        $optionalModelRead = new FieldModelOptional($reader, 0, $valueModelRead);

        $this->assertFalse($optionalModelRead->hasValue());
        $this->assertNull($optionalModelRead->get());
    }

    public function testFieldModelOptionalStringWithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional string field model
        $valueModel = new FieldModelString($writer, 1);
        $optionalModel = new FieldModelOptional($writer, 0, $valueModel);

        // Set value
        $optionalModel->set("Hello Optional");

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FieldModelString($reader, 1);
        $optionalModelRead = new FieldModelOptional($reader, 0, $valueModelRead);

        $this->assertTrue($optionalModelRead->hasValue());
        $this->assertSame("Hello Optional", $optionalModelRead->get());
    }

    public function testFieldModelOptionalStringNull(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional string field model
        $valueModel = new FieldModelString($writer, 1);
        $optionalModel = new FieldModelOptional($writer, 0, $valueModel);

        // Set null
        $optionalModel->set(null);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FieldModelString($reader, 1);
        $optionalModelRead = new FieldModelOptional($reader, 0, $valueModelRead);

        $this->assertFalse($optionalModelRead->hasValue());
        $this->assertNull($optionalModelRead->get());
    }

    public function testFinalModelOptionalInt32WithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional int32 final model
        $valueModel = new FinalModelInt32($writer, 0);
        $optionalModel = new FinalModelOptional($writer, 0, $valueModel);

        // Set value
        $optionalModel->set(99);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FinalModelInt32($reader, 0);
        $optionalModelRead = new FinalModelOptional($reader, 0, $valueModelRead);

        $this->assertTrue($optionalModelRead->hasValue());
        $this->assertSame(99, $optionalModelRead->get());
    }

    public function testFinalModelOptionalInt32Null(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional int32 final model
        $valueModel = new FinalModelInt32($writer, 0);
        $optionalModel = new FinalModelOptional($writer, 0, $valueModel);

        // Set null
        $optionalModel->set(null);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FinalModelInt32($reader, 0);
        $optionalModelRead = new FinalModelOptional($reader, 0, $valueModelRead);

        $this->assertFalse($optionalModelRead->hasValue());
        $this->assertNull($optionalModelRead->get());
    }

    public function testFinalModelOptionalStringWithValue(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional string final model
        $valueModel = new FinalModelString($writer, 0);
        $optionalModel = new FinalModelOptional($writer, 0, $valueModel);

        // Set value
        $optionalModel->set("Final Optional");

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FinalModelString($reader, 0);
        $optionalModelRead = new FinalModelOptional($reader, 0, $valueModelRead);

        $this->assertTrue($optionalModelRead->hasValue());
        $this->assertSame("Final Optional", $optionalModelRead->get());
    }

    public function testFinalModelOptionalStringNull(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(100);

        // Create optional string final model
        $valueModel = new FinalModelString($writer, 0);
        $optionalModel = new FinalModelOptional($writer, 0, $valueModel);

        // Set null
        $optionalModel->set(null);

        // Read back
        $reader = new ReadBuffer($writer->data());

        $valueModelRead = new FinalModelString($reader, 0);
        $optionalModelRead = new FinalModelOptional($reader, 0, $valueModelRead);

        $this->assertFalse($optionalModelRead->hasValue());
        $this->assertNull($optionalModelRead->get());
    }
}


