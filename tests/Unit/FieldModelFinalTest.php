<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Final\{FieldModelInt32, FieldModelDouble, FieldModelString};
use PHPUnit\Framework\TestCase;

class FieldModelFinalTest extends TestCase
{
    public function testFieldModelInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelInt32($writeBuffer, 0);
        $this->assertEquals(4, $field->size());

        $field->set(54321);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelInt32($readBuffer, 0);

        $this->assertEquals(54321, $readField->get());
    }

    /**
     * CRITICAL: Test Final format uses INLINE for strings (no pointers!)
     */
    public function testFieldModelStringInline(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelString($writeBuffer, 0);
        $field->set('Hello Final');

        // Final format: size includes data (4 + N bytes)
        $this->assertEquals(15, $field->size()); // 4 + 11 bytes
        $this->assertEquals(0, $field->extra()); // No extra (already in size)

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelString($readBuffer, 0);

        $this->assertEquals('Hello Final', $readField->get());
        $this->assertEquals(15, $readField->size());
    }

    public function testFieldModelStringEmpty(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FieldModelString($writeBuffer, 0);
        $field->set('');

        $this->assertEquals(4, $field->size()); // Just size header

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FieldModelString($readBuffer, 0);

        $this->assertEquals('', $readField->get());
    }

    /**
     * Compare Standard vs Final size difference
     */
    public function testSizeDifference(): void
    {
        $testString = 'Test';

        // Standard format (pointer-based)
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(100);
        $fieldStd = new \FBE\Standard\FieldModelString($writeBufferStd, 0);
        $fieldStd->set($testString);

        // Final format (inline)
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(100);
        $fieldFinal = new FieldModelString($writeBufferFinal, 0);
        $fieldFinal->set($testString);

        // Standard: 4-byte pointer + (4-byte size + 4-byte data) = 12 bytes total
        // Final: 4-byte size + 4-byte data = 8 bytes total
        $this->assertEquals(4, $fieldStd->size()); // Pointer only
        $this->assertGreaterThan(0, $fieldStd->extra()); // Has extra

        $this->assertEquals(8, $fieldFinal->size()); // Inline size+data
        $this->assertEquals(0, $fieldFinal->extra()); // No extra

        // Final format is more compact!
        $this->assertLessThan($fieldStd->total(), $fieldFinal->total());
    }

    public function testMultipleFieldsSequential(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $offset = 0;

        // Write int32
        $field1 = new FieldModelInt32($writeBuffer, $offset);
        $field1->set(999);
        $offset += $field1->size();

        // Write string (inline)
        $field2 = new FieldModelString($writeBuffer, $offset);
        $field2->set('Final');
        $offset += $field2->size();

        // Write double
        $field3 = new FieldModelDouble($writeBuffer, $offset);
        $field3->set(1.23);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $offset = 0;

        $read1 = new FieldModelInt32($readBuffer, $offset);
        $this->assertEquals(999, $read1->get());
        $offset += $read1->size();

        $read2 = new FieldModelString($readBuffer, $offset);
        $this->assertEquals('Final', $read2->get());
        $offset += $read2->size();

        $read3 = new FieldModelDouble($readBuffer, $offset);
        $this->assertEqualsWithDelta(1.23, $read3->get(), 0.01);
    }
}
