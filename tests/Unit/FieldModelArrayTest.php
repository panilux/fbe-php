<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{ReadBuffer, WriteBuffer};
use FBE\Standard;
use FBE\Final;
use PHPUnit\Framework\TestCase;

class FieldModelArrayTest extends TestCase
{
    public function testStandardArrayInt32(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Standard\FieldModelArrayInt32($buffer, 0, 5);

        $values = [1, 2, 3, 4, 5];
        $model->set($values);

        $this->assertEquals(20, $model->size()); // 5 × 4 = 20 bytes

        $readBuffer = new ReadBuffer($buffer->data());
        $readModel = new Standard\FieldModelArrayInt32($readBuffer, 0, 5);

        $this->assertEquals($values, $readModel->get());
    }

    public function testFinalArrayInt32(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Final\FieldModelArrayInt32($buffer, 0, 5);

        $values = [10, 20, 30, 40, 50];
        $model->set($values);

        $this->assertEquals(20, $model->size()); // 5 × 4 = 20 bytes

        $readBuffer = new ReadBuffer($buffer->data());
        $readModel = new Final\FieldModelArrayInt32($readBuffer, 0, 5);

        $this->assertEquals($values, $readModel->get());
    }

    public function testStandardArrayString(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Standard\FieldModelArrayString($buffer, 0, 3);

        $values = ['Alice', 'Bob', 'Charlie'];
        $model->set($values);

        $this->assertEquals(12, $model->size()); // 3 × 4 = 12 bytes (pointers)
        $this->assertGreaterThan(0, $model->extra()); // String data

        $readBuffer = new ReadBuffer($buffer->data());
        $readModel = new Standard\FieldModelArrayString($readBuffer, 0, 3);

        $this->assertEquals($values, $readModel->get());
    }

    public function testFinalArrayString(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Final\FieldModelArrayString($buffer, 0, 3);

        $values = ['A', 'BB', 'CCC'];
        $model->set($values);

        $readBuffer = new ReadBuffer($buffer->data());
        $readModel = new Final\FieldModelArrayString($readBuffer, 0, 3);

        $result = $readModel->get();
        $this->assertEquals($values, $result);
    }

    public function testEmptyStringArray(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Final\FieldModelArrayString($buffer, 0, 2);

        $values = ['', ''];
        $model->set($values);

        $readBuffer = new ReadBuffer($buffer->data());
        $readModel = new Final\FieldModelArrayString($readBuffer, 0, 2);

        $this->assertEquals($values, $readModel->get());
    }

    public function testArraySizeMismatchThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array size mismatch');

        $buffer = new WriteBuffer(256);
        $model = new Standard\FieldModelArrayInt32($buffer, 0, 5);

        // Try to set 3 elements when array size is 5
        $model->set([1, 2, 3]);
    }

    public function testFixedArraySize(): void
    {
        $buffer = new WriteBuffer(256);
        $model = new Standard\FieldModelArrayInt32($buffer, 0, 10);

        $this->assertEquals(10, $model->arraySize());
        $this->assertEquals(40, $model->size()); // 10 × 4 = 40 bytes
    }

    public function testStandardVsFinalArrayInt32Identical(): void
    {
        $values = [100, 200, 300];

        // Standard
        $stdBuffer = new WriteBuffer(256);
        $stdModel = new Standard\FieldModelArrayInt32($stdBuffer, 0, 3);
        $stdModel->set($values);

        // Final
        $finalBuffer = new WriteBuffer(256);
        $finalModel = new Final\FieldModelArrayInt32($finalBuffer, 0, 3);
        $finalModel->set($values);

        // For primitives, Standard and Final should be identical
        $this->assertEquals($stdBuffer->data(), $finalBuffer->data());
    }

    public function testStandardVsFinalArrayStringDifferent(): void
    {
        $values = ['test', 'data'];

        // Standard
        $stdBuffer = new WriteBuffer(256);
        $stdModel = new Standard\FieldModelArrayString($stdBuffer, 0, 2);
        $stdModel->set($values);

        // Final
        $finalBuffer = new WriteBuffer(256);
        $finalModel = new Final\FieldModelArrayString($finalBuffer, 0, 2);
        $finalModel->set($values);

        // For strings, Standard uses pointers, Final is inline
        $this->assertNotEquals($stdBuffer->data(), $finalBuffer->data());

        // But Final should be smaller or equal
        $this->assertLessThanOrEqual(strlen($stdBuffer->data()), strlen($finalBuffer->data()));
    }
}
