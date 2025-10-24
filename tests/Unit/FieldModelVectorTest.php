<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{FieldModelVectorInt32 as StdVectorInt32, FieldModelVectorString as StdVectorString};
use FBE\Final\{FieldModelVectorInt32 as FinalVectorInt32, FieldModelVectorString as FinalVectorString};
use PHPUnit\Framework\TestCase;

class FieldModelVectorTest extends TestCase
{
    public function testStandardVectorInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $vector = new StdVectorInt32($writeBuffer, 0);
        $vector->set([10, 20, 30, 40, 50]);

        $this->assertEquals(4, $vector->size()); // Pointer only
        $this->assertEquals(5, $vector->count());
        $this->assertGreaterThan(0, $vector->extra()); // Has extra data

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readVector = new StdVectorInt32($readBuffer, 0);

        $this->assertEquals([10, 20, 30, 40, 50], $readVector->get());
        $this->assertEquals(5, $readVector->count());
    }

    public function testStandardVectorString(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(300);

        $vector = new StdVectorString($writeBuffer, 0);
        $vector->set(['Hello', 'World', 'FBE']);

        $this->assertEquals(4, $vector->size()); // Pointer only
        $this->assertEquals(3, $vector->count());
        $this->assertGreaterThan(0, $vector->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readVector = new StdVectorString($readBuffer, 0);

        $this->assertEquals(['Hello', 'World', 'FBE'], $readVector->get());
        $this->assertEquals(3, $readVector->count());
    }

    public function testFinalVectorInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $vector = new FinalVectorInt32($writeBuffer, 0);
        $vector->set([10, 20, 30, 40, 50]);

        // Final: 4 (count) + 5*4 (elements) = 24 bytes
        $this->assertEquals(24, $vector->size());
        $this->assertEquals(0, $vector->extra()); // No extra (inline)
        $this->assertEquals(5, $vector->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readVector = new FinalVectorInt32($readBuffer, 0);

        $this->assertEquals([10, 20, 30, 40, 50], $readVector->get());
        $this->assertEquals(5, $readVector->count());
    }

    public function testFinalVectorString(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(300);

        $vector = new FinalVectorString($writeBuffer, 0);
        $vector->set(['Hello', 'World', 'FBE']);

        // Final: 4 (count) + (4+5) + (4+5) + (4+3) = 29 bytes
        $this->assertEquals(29, $vector->size());
        $this->assertEquals(0, $vector->extra());
        $this->assertEquals(3, $vector->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readVector = new FinalVectorString($readBuffer, 0);

        $this->assertEquals(['Hello', 'World', 'FBE'], $readVector->get());
        $this->assertEquals(3, $readVector->count());
    }

    public function testEmptyVectors(): void
    {
        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(100);
        $vectorStd = new StdVectorInt32($writeBufferStd, 0);
        $vectorStd->set([]);

        $this->assertEquals(0, $vectorStd->count());

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(100);
        $vectorFinal = new FinalVectorInt32($writeBufferFinal, 0);
        $vectorFinal->set([]);

        $this->assertEquals(4, $vectorFinal->size()); // Just count
        $this->assertEquals(0, $vectorFinal->count());
    }

    /**
     * Compare Standard vs Final size for Vector<Int32>
     */
    public function testStandardVsFinalSize(): void
    {
        $data = [1, 2, 3, 4, 5];

        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(200);
        $vectorStd = new StdVectorInt32($writeBufferStd, 0);
        $vectorStd->set($data);

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(200);
        $vectorFinal = new FinalVectorInt32($writeBufferFinal, 0);
        $vectorFinal->set($data);

        // Standard: 4 (pointer) + 4 (count) + 5*4 (elements) = 28 bytes total
        // Final: 4 (count) + 5*4 (elements) = 24 bytes total

        $this->assertEquals(28, $vectorStd->total());
        $this->assertEquals(24, $vectorFinal->total());

        // Final is more compact!
        $this->assertLessThan($vectorStd->total(), $vectorFinal->total());
    }

    public function testVectorOfStringsSize(): void
    {
        $data = ['A', 'BB', 'CCC'];

        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(300);
        $vectorStd = new StdVectorString($writeBufferStd, 0);
        $vectorStd->set($data);

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(300);
        $vectorFinal = new FinalVectorString($writeBufferFinal, 0);
        $vectorFinal->set($data);

        // Standard: 4 (vec_ptr) + 4 (count) + 3*4 (str_ptrs) + 3*(4+size) = complex with pointers
        // Final: 4 (count) + (4+1) + (4+2) + (4+3) = 4 + 5 + 6 + 7 = 22 bytes

        $stdTotal = $vectorStd->total();
        $finalTotal = $vectorFinal->total();

        $this->assertEquals(22, $finalTotal);
        // Final is more compact (no pointers)
        $this->assertLessThan($stdTotal, $finalTotal);
    }
}
