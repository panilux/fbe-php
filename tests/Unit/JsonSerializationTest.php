<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use PHPUnit\Framework\TestCase;
use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{
    FieldModelInt32,
    FieldModelInt64,
    FieldModelFloat,
    FieldModelDouble,
    FieldModelBool,
    FieldModelString,
    FieldModelBytes,
    FieldModelUuid,
    FieldModelDecimal,
    FieldModelTimestamp,
    FieldModelInt8,
    FieldModelUInt8,
    FieldModelInt16,
    FieldModelUInt16,
    FieldModelUInt32,
    FieldModelUInt64,
    FieldModelChar,
    FieldModelWChar
};
use FBE\Types\{Uuid, Decimal};

class JsonSerializationTest extends TestCase
{
    // Test primitive types
    public function testInt32Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelInt32($buffer, 0);
        $field->set(42);

        // toJson
        $json = $field->toJson();
        $this->assertSame(42, $json);

        // fromJson
        $field2 = new FieldModelInt32($buffer, 10);
        $field2->fromJson(99);

        // Read back using ReadBuffer
        $readBuffer = new ReadBuffer($buffer->data());
        $field3 = new FieldModelInt32($readBuffer, 10);
        $this->assertSame(99, $field3->get());
    }

    public function testInt64Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelInt64($buffer, 0);
        $field->set(123456789);

        $json = $field->toJson();
        $this->assertSame(123456789, $json);
    }

    public function testFloatJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelFloat($buffer, 0);
        $field->set(3.14);

        $json = $field->toJson();
        $this->assertEqualsWithDelta(3.14, $json, 0.01);

        // fromJson with int (should convert)
        $field2 = new FieldModelFloat($buffer, 10);
        $field2->fromJson(42);
        $this->assertSame(42.0, $field2->get());
    }

    public function testDoubleJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelDouble($buffer, 0);
        $field->set(2.718281828);

        $json = $field->toJson();
        $this->assertEqualsWithDelta(2.718281828, $json, 0.000001);
    }

    public function testBoolJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelBool($buffer, 0);
        $field->set(true);

        $json = $field->toJson();
        $this->assertTrue($json);

        // fromJson
        $field2 = new FieldModelBool($buffer, 10);
        $field2->fromJson(false);
        $this->assertFalse($field2->get());
    }

    // Test complex types
    public function testStringJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(200);

        $field = new FieldModelString($buffer, 0);
        $field->set('Hello, JSON!');

        $json = $field->toJson();
        $this->assertSame('Hello, JSON!', $json);

        // fromJson
        $readBuffer = new ReadBuffer($buffer->data());
        $field2 = new FieldModelString($readBuffer, 0);

        $buffer2 = new WriteBuffer();
        $buffer2->allocate(200);
        $field3 = new FieldModelString($buffer2, 0);
        $field3->fromJson('Test');
        $this->assertSame('Test', $field3->get());
    }

    public function testBytesJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(200);

        $field = new FieldModelBytes($buffer, 0);
        $field->set('binary data \x00\x01\x02');

        $json = $field->toJson();
        $this->assertIsString($json);

        // Verify it's base64
        $decoded = base64_decode($json, true);
        $this->assertNotFalse($decoded);
        $this->assertSame('binary data \x00\x01\x02', $decoded);

        // fromJson
        $buffer2 = new WriteBuffer();
        $buffer2->allocate(200);
        $field2 = new FieldModelBytes($buffer2, 0);
        $field2->fromJson($json);

        $readBuffer2 = new ReadBuffer($buffer2->data());
        $field3 = new FieldModelBytes($readBuffer2, 0);
        $this->assertSame('binary data \x00\x01\x02', $field3->get());
    }

    public function testUuidJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');
        $field = new FieldModelUuid($buffer, 0);
        $field->set($uuid);

        $json = $field->toJson();
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $json);

        // fromJson
        $buffer2 = new WriteBuffer();
        $buffer2->allocate(100);
        $field2 = new FieldModelUuid($buffer2, 0);
        $field2->fromJson('123e4567-e89b-12d3-a456-426614174000');

        $readBuffer2 = new ReadBuffer($buffer2->data());
        $field3 = new FieldModelUuid($readBuffer2, 0);
        $this->assertSame('123e4567-e89b-12d3-a456-426614174000', $field3->get()->toString());
    }

    public function testDecimalJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $decimal = Decimal::fromString('123.45');
        $field = new FieldModelDecimal($buffer, 0);
        $field->set($decimal);

        $json = $field->toJson();
        $this->assertSame('123.45', $json);

        // fromJson
        $buffer2 = new WriteBuffer();
        $buffer2->allocate(100);
        $field2 = new FieldModelDecimal($buffer2, 0);
        $field2->fromJson('-456.789');

        $readBuffer2 = new ReadBuffer($buffer2->data());
        $field3 = new FieldModelDecimal($readBuffer2, 0);
        $this->assertSame('-456.789', $field3->get()->toString());
    }

    public function testTimestampJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $nanoseconds = 1234567890000000000; // ~2009-02-13
        $field = new FieldModelTimestamp($buffer, 0);
        $field->set($nanoseconds);

        $json = $field->toJson();
        $this->assertSame($nanoseconds, $json);

        // fromJson with smaller value to avoid float precision issues
        $buffer2 = new WriteBuffer();
        $buffer2->allocate(100);
        $field2 = new FieldModelTimestamp($buffer2, 0);
        $timestamp2 = 1700000000000000000; // Smaller value that stays as int
        $field2->fromJson($timestamp2);

        $readBuffer2 = new ReadBuffer($buffer2->data());
        $field3 = new FieldModelTimestamp($readBuffer2, 0);
        $this->assertSame($timestamp2, $field3->get());
    }

    // Test complete JSON encode/decode workflow
    public function testCompleteJsonWorkflow(): void
    {
        // Create a buffer and write data
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $intField = new FieldModelInt32($writeBuffer, 0);
        $intField->set(42);

        $stringField = new FieldModelString($writeBuffer, 10);
        $stringField->set('Hello World');

        $floatField = new FieldModelDouble($writeBuffer, 100);
        $floatField->set(3.14159);

        // Export to JSON
        $jsonData = [
            'number' => $intField->toJson(),
            'text' => $stringField->toJson(),
            'pi' => $floatField->toJson(),
        ];

        $jsonString = json_encode($jsonData);
        $this->assertIsString($jsonString);

        // Decode JSON
        $decoded = json_decode($jsonString, true);
        $this->assertIsArray($decoded);
        $this->assertSame(42, $decoded['number']);
        $this->assertSame('Hello World', $decoded['text']);
        $this->assertEqualsWithDelta(3.14159, $decoded['pi'], 0.00001);

        // Import from JSON
        $writeBuffer2 = new WriteBuffer();
        $writeBuffer2->allocate(500);

        $intField2 = new FieldModelInt32($writeBuffer2, 0);
        $intField2->fromJson($decoded['number']);

        $this->assertSame(42, $intField2->get());
    }

    // Test type validation
    public function testFromJsonTypeValidation(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        // Int32 with wrong type
        $field = new FieldModelInt32($buffer, 0);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected int');
        $field->fromJson('not an int');
    }

    public function testBoolFromJsonTypeValidation(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelBool($buffer, 0);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected bool');
        $field->fromJson(1); // int, not bool
    }

    public function testStringFromJsonTypeValidation(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelString($buffer, 0);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected string');
        $field->fromJson(123);
    }

    // Test new primitive types
    public function testInt8Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelInt8($buffer, 0);
        $field->set(-42);

        $json = $field->toJson();
        $this->assertSame(-42, $json);

        // fromJson
        $field2 = new FieldModelInt8($buffer, 10);
        $field2->fromJson(-100);
        $this->assertSame(-100, $field2->get());
    }

    public function testUInt8Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelUInt8($buffer, 0);
        $field->set(200);

        $json = $field->toJson();
        $this->assertSame(200, $json);

        // fromJson
        $field2 = new FieldModelUInt8($buffer, 10);
        $field2->fromJson(255);
        $this->assertSame(255, $field2->get());
    }

    public function testInt16Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelInt16($buffer, 0);
        $field->set(-12345);

        $json = $field->toJson();
        $this->assertSame(-12345, $json);

        // fromJson
        $field2 = new FieldModelInt16($buffer, 10);
        $field2->fromJson(30000);
        $this->assertSame(30000, $field2->get());
    }

    public function testUInt16Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelUInt16($buffer, 0);
        $field->set(54321);

        $json = $field->toJson();
        $this->assertSame(54321, $json);

        // fromJson
        $field2 = new FieldModelUInt16($buffer, 10);
        $field2->fromJson(65000);
        $this->assertSame(65000, $field2->get());
    }

    public function testUInt32Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelUInt32($buffer, 0);
        $field->set(3000000000);

        $json = $field->toJson();
        $this->assertSame(3000000000, $json);

        // fromJson
        $field2 = new FieldModelUInt32($buffer, 10);
        $field2->fromJson(4000000000);
        $this->assertSame(4000000000, $field2->get());
    }

    public function testUInt64Json(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelUInt64($buffer, 0);
        $field->set(1234567890123);

        $json = $field->toJson();
        $this->assertSame(1234567890123, $json);

        // fromJson
        $field2 = new FieldModelUInt64($buffer, 10);
        $field2->fromJson(9876543210987);
        $this->assertSame(9876543210987, $field2->get());
    }

    public function testCharJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelChar($buffer, 0);
        $field->set(65); // 'A'

        $json = $field->toJson();
        $this->assertSame(65, $json);

        // fromJson
        $field2 = new FieldModelChar($buffer, 10);
        $field2->fromJson(90); // 'Z'
        $this->assertSame(90, $field2->get());
    }

    public function testWCharJson(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $field = new FieldModelWChar($buffer, 0);
        $field->set(0x1F600); // 😀

        $json = $field->toJson();
        $this->assertSame(0x1F600, $json);

        // fromJson
        $field2 = new FieldModelWChar($buffer, 10);
        $field2->fromJson(0x4E2D); // 中
        $this->assertSame(0x4E2D, $field2->get());
    }
}
