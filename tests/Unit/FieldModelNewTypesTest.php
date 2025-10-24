<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{ReadBuffer, WriteBuffer};
use FBE\Standard;
use FBE\Final;
use PHPUnit\Framework\TestCase;

/**
 * Tests for new FieldModel types (int8, uint8, int16, uint16, uint32, uint64, char, wchar)
 */
class FieldModelNewTypesTest extends TestCase
{
    public function testFieldModelInt8Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelInt8($buffer, 0);

        $field->set(-42);
        $this->assertEquals(1, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelInt8($readBuffer, 0);
        $this->assertEquals(-42, $readField->get());
    }

    public function testFieldModelUInt8Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelUInt8($buffer, 0);

        $field->set(200);
        $this->assertEquals(1, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelUInt8($readBuffer, 0);
        $this->assertEquals(200, $readField->get());
    }

    public function testFieldModelInt16Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelInt16($buffer, 0);

        $field->set(-12345);
        $this->assertEquals(2, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelInt16($readBuffer, 0);
        $this->assertEquals(-12345, $readField->get());
    }

    public function testFieldModelUInt16Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelUInt16($buffer, 0);

        $field->set(54321);
        $this->assertEquals(2, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelUInt16($readBuffer, 0);
        $this->assertEquals(54321, $readField->get());
    }

    public function testFieldModelUInt32Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelUInt32($buffer, 0);

        $field->set(3000000000);
        $this->assertEquals(4, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelUInt32($readBuffer, 0);
        $this->assertEquals(3000000000, $readField->get());
    }

    public function testFieldModelUInt64Standard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelUInt64($buffer, 0);

        $field->set(1234567890123);
        $this->assertEquals(8, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelUInt64($readBuffer, 0);
        $this->assertEquals(1234567890123, $readField->get());
    }

    public function testFieldModelCharStandard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelChar($buffer, 0);

        $field->set(65); // 'A'
        $this->assertEquals(1, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelChar($readBuffer, 0);
        $this->assertEquals(65, $readField->get());
    }

    public function testFieldModelWCharStandard(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Standard\FieldModelWChar($buffer, 0);

        $field->set(0x1F600); // 😀
        $this->assertEquals(4, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Standard\FieldModelWChar($readBuffer, 0);
        $this->assertEquals(0x1F600, $readField->get());
    }

    // Final format tests (identical to Standard for primitives)

    public function testFieldModelInt8Final(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Final\FieldModelInt8($buffer, 0);

        $field->set(-100);
        $this->assertEquals(1, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Final\FieldModelInt8($readBuffer, 0);
        $this->assertEquals(-100, $readField->get());
    }

    public function testFieldModelUInt32Final(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Final\FieldModelUInt32($buffer, 0);

        $field->set(4000000000);
        $this->assertEquals(4, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Final\FieldModelUInt32($readBuffer, 0);
        $this->assertEquals(4000000000, $readField->get());
    }

    public function testFieldModelWCharFinal(): void
    {
        $buffer = new WriteBuffer(10);
        $field = new Final\FieldModelWChar($buffer, 0);

        $field->set(0x4E2D); // 中
        $this->assertEquals(4, $field->size());

        $readBuffer = new ReadBuffer($buffer->data());
        $readField = new Final\FieldModelWChar($readBuffer, 0);
        $this->assertEquals(0x4E2D, $readField->get());
    }

    public function testStandardVsFinalIdentical(): void
    {
        // For primitives, Standard and Final should produce identical binary
        $value = 12345;

        $stdBuffer = new WriteBuffer(10);
        $stdField = new Standard\FieldModelInt16($stdBuffer, 0);
        $stdField->set($value);

        $finalBuffer = new WriteBuffer(10);
        $finalField = new Final\FieldModelInt16($finalBuffer, 0);
        $finalField->set($value);

        // Binary should be identical for primitives
        $this->assertEquals($stdBuffer->data(), $finalBuffer->data());
    }
}
