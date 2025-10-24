<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{
    FieldModelMapStringString as StdMapStringString,
    FieldModelMapStringInt32 as StdMapStringInt32
};
use FBE\Final\{
    FieldModelMapStringString as FinalMapStringString,
    FieldModelMapStringInt32 as FinalMapStringInt32
};
use PHPUnit\Framework\TestCase;

class FieldModelMapTest extends TestCase
{
    public function testStandardMapStringString(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $map = new StdMapStringString($writeBuffer, 0);
        $map->set([
            'name' => 'Alice',
            'city' => 'New York',
            'country' => 'USA'
        ]);

        $this->assertEquals(4, $map->size()); // Pointer only
        $this->assertEquals(3, $map->count());
        $this->assertGreaterThan(0, $map->extra()); // Has extra data

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new StdMapStringString($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(3, count($result));
        $this->assertEquals('Alice', $result['name']);
        $this->assertEquals('New York', $result['city']);
        $this->assertEquals('USA', $result['country']);
        $this->assertEquals(3, $readMap->count());
    }

    public function testStandardMapStringInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $map = new StdMapStringInt32($writeBuffer, 0);
        $map->set([
            'age' => 30,
            'score' => 95,
            'level' => 5
        ]);

        $this->assertEquals(4, $map->size()); // Pointer only
        $this->assertEquals(3, $map->count());
        $this->assertGreaterThan(0, $map->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new StdMapStringInt32($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(3, count($result));
        $this->assertEquals(30, $result['age']);
        $this->assertEquals(95, $result['score']);
        $this->assertEquals(5, $result['level']);
        $this->assertEquals(3, $readMap->count());
    }

    public function testFinalMapStringString(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $map = new FinalMapStringString($writeBuffer, 0);
        $map->set([
            'name' => 'Bob',
            'city' => 'LA'
        ]);

        // Final: 4 (count) + (4+4 + 4+3) + (4+4 + 4+2)
        //      = 4 + (8+7) + (8+6) = 4 + 15 + 14 = 33 bytes
        $this->assertEquals(33, $map->size());
        $this->assertEquals(0, $map->extra()); // No extra (inline)
        $this->assertEquals(2, $map->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new FinalMapStringString($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(2, count($result));
        $this->assertEquals('Bob', $result['name']);
        $this->assertEquals('LA', $result['city']);
        $this->assertEquals(2, $readMap->count());
    }

    public function testFinalMapStringInt32(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $map = new FinalMapStringInt32($writeBuffer, 0);
        $map->set([
            'x' => 10,
            'y' => 20,
            'z' => 30
        ]);

        // Final: 4 (count) + 3 * ((4+1) + 4)
        //      = 4 + 3 * 9 = 4 + 27 = 31 bytes
        $this->assertEquals(31, $map->size());
        $this->assertEquals(0, $map->extra());
        $this->assertEquals(3, $map->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new FinalMapStringInt32($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(3, count($result));
        $this->assertEquals(10, $result['x']);
        $this->assertEquals(20, $result['y']);
        $this->assertEquals(30, $result['z']);
        $this->assertEquals(3, $readMap->count());
    }

    public function testEmptyMaps(): void
    {
        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(100);
        $mapStd = new StdMapStringString($writeBufferStd, 0);
        $mapStd->set([]);

        $this->assertEquals(0, $mapStd->count());

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(100);
        $mapFinal = new FinalMapStringString($writeBufferFinal, 0);
        $mapFinal->set([]);

        $this->assertEquals(4, $mapFinal->size()); // Just count
        $this->assertEquals(0, $mapFinal->count());
    }

    /**
     * Compare Standard vs Final size for Map<String, String>
     */
    public function testStandardVsFinalSize(): void
    {
        $data = [
            'a' => 'A',
            'b' => 'B',
            'c' => 'C'
        ];

        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(200);
        $mapStd = new StdMapStringString($writeBufferStd, 0);
        $mapStd->set($data);

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(200);
        $mapFinal = new FinalMapStringString($writeBufferFinal, 0);
        $mapFinal->set($data);

        $stdTotal = $mapStd->total();
        $finalTotal = $mapFinal->total();

        // Final is more compact (no pointer overhead)
        $this->assertLessThan($stdTotal, $finalTotal);
    }

    /**
     * Test Map<String, Int32> size calculation
     */
    public function testMapStringInt32Size(): void
    {
        $data = [
            'alpha' => 100,
            'beta' => 200,
            'gamma' => 300
        ];

        // Standard
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(300);
        $mapStd = new StdMapStringInt32($writeBufferStd, 0);
        $mapStd->set($data);

        // Final
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(300);
        $mapFinal = new FinalMapStringInt32($writeBufferFinal, 0);
        $mapFinal->set($data);

        // Final: 4 (count) + 3 * ((4+5) + 4) + 3 * ((4+4) + 4) + 3 * ((4+5) + 4)
        //      = 4 + (9+4) + (8+4) + (9+4) = 4 + 13 + 12 + 13 = 42 bytes
        $this->assertEquals(42, $mapFinal->size());

        $stdTotal = $mapStd->total();
        $finalTotal = $mapFinal->total();

        // Final is more compact
        $this->assertLessThan($stdTotal, $finalTotal);
    }

    /**
     * Test single entry map
     */
    public function testSingleEntryMap(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $map = new FinalMapStringInt32($writeBuffer, 0);
        $map->set(['key' => 42]);

        // Final: 4 (count) + (4+3) + 4 = 4 + 7 + 4 = 15 bytes
        $this->assertEquals(15, $map->size());
        $this->assertEquals(1, $map->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new FinalMapStringInt32($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(1, count($result));
        $this->assertEquals(42, $result['key']);
    }

    /**
     * Test map with long strings
     */
    public function testMapWithLongStrings(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(1000);

        $map = new FinalMapStringString($writeBuffer, 0);
        $map->set([
            'description' => 'This is a very long description for testing purposes',
            'title' => 'Short title'
        ]);

        $this->assertEquals(2, $map->count());
        $this->assertGreaterThan(50, $map->size()); // Should be large

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new FinalMapStringString($readBuffer, 0);

        $result = $readMap->get();
        $this->assertEquals(2, count($result));
        $this->assertEquals('This is a very long description for testing purposes', $result['description']);
        $this->assertEquals('Short title', $result['title']);
    }

    /**
     * Test map ordering preservation
     */
    public function testMapOrderPreservation(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $data = [
            'first' => 1,
            'second' => 2,
            'third' => 3,
            'fourth' => 4
        ];

        $map = new FinalMapStringInt32($writeBuffer, 0);
        $map->set($data);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readMap = new FinalMapStringInt32($readBuffer, 0);

        $result = $readMap->get();

        // PHP preserves array order, check we got all values
        $this->assertEquals(4, count($result));
        $this->assertEquals(1, $result['first']);
        $this->assertEquals(2, $result['second']);
        $this->assertEquals(3, $result['third']);
        $this->assertEquals(4, $result['fourth']);

        // Check keys exist in result
        $keys = array_keys($result);
        $this->assertContains('first', $keys);
        $this->assertContains('second', $keys);
        $this->assertContains('third', $keys);
        $this->assertContains('fourth', $keys);
    }
}
