<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelInt32;
use FBE\FieldModelString;
use FBE\FinalModelInt32;
use FBE\FinalModelString;

final class StructTest extends TestCase
{
    public function testFieldModelStructSimple(): void
    {
        // Create a simple struct: { id: 42, name: "test" }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write id (int32) at offset 0
        $fieldId = new FieldModelInt32($writer, 0);
        $fieldId->set(42);
        
        // Write name (string) at offset 4
        $fieldName = new FieldModelString($writer, 4);
        $fieldName->set("test");
        
        $size = 4 + $fieldId->extra() + 4 + $fieldName->extra();
        
        // Read back
        $reader = new ReadBuffer($writer->data(), 0, $size);
        
        $readId = new FieldModelInt32($reader, 0);
        $this->assertEquals(42, $readId->get());
        
        $readName = new FieldModelString($reader, 4);
        $this->assertEquals("test", $readName->get());
    }
    
    public function testFieldModelStructWithMultipleFields(): void
    {
        // Struct: { count: 100, label: "items", active: true }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write count (int32) at offset 0
        $writer->writeInt32(0, 100);
        
        // Write label (string) at offset 4
        // String format: [length:4][data:n]
        $label = "items";
        $writer->writeInt32(4, strlen($label));
        for ($i = 0; $i < strlen($label); $i++) {
            $writer->writeByte(8 + $i, ord($label[$i]));
        }
        
        // Write active (bool) at offset 8 + strlen
        $writer->writeBool(8 + strlen($label), true);
        
        // Read back
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $this->assertEquals(100, $reader->readInt32(0));
        $this->assertEquals(5, $reader->readInt32(4)); // length
        $this->assertEquals("items", $reader->readString(4));
        $this->assertTrue($reader->readBool(8 + 5));
    }
    
    public function testFinalModelStructSimple(): void
    {
        // FinalModel uses inline format (no pointers)
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write id (int32) - inline, just 4 bytes
        $fieldId = new FinalModelInt32($writer, 0);
        $fieldId->set(99);
        
        // Write name (string) - inline with length prefix
        $fieldName = new FinalModelString($writer, 4);
        $fieldName->set("final");
        
        // Read back
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $readId = new FinalModelInt32($reader, 0);
        $this->assertEquals(99, $readId->get());
        
        $readName = new FinalModelString($reader, 4);
        $this->assertEquals("final", $readName->get());
    }
    
    public function testNestedStruct(): void
    {
        // Outer struct: { inner: { x: 10, y: 20 }, label: "outer" }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write inner.x at offset 0
        $writer->writeInt32(0, 10);
        
        // Write inner.y at offset 4
        $writer->writeInt32(4, 20);
        
        // Write label at offset 8
        $label = "outer";
        $writer->writeInt32(8, strlen($label));
        for ($i = 0; $i < strlen($label); $i++) {
            $writer->writeByte(12 + $i, ord($label[$i]));
        }
        
        // Read back
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        // Read inner struct
        $innerX = $reader->readInt32(0);
        $innerY = $reader->readInt32(4);
        $this->assertEquals(10, $innerX);
        $this->assertEquals(20, $innerY);
        
        // Read outer label
        $outerLabel = $reader->readString(8);
        $this->assertEquals("outer", $outerLabel);
    }
    
    public function testStructWithArray(): void
    {
        // Struct: { values: [1, 2, 3], count: 3 }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write array length at offset 0
        $writer->writeInt32(0, 3);
        
        // Write array elements at offset 4
        $writer->writeInt32(4, 1);
        $writer->writeInt32(8, 2);
        $writer->writeInt32(12, 3);
        
        // Write count at offset 16
        $writer->writeInt32(16, 3);
        
        // Read back
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $arrayLength = $reader->readInt32(0);
        $this->assertEquals(3, $arrayLength);
        
        $values = [];
        for ($i = 0; $i < $arrayLength; $i++) {
            $values[] = $reader->readInt32(4 + $i * 4);
        }
        $this->assertEquals([1, 2, 3], $values);
        
        $count = $reader->readInt32(16);
        $this->assertEquals(3, $count);
    }
    
    public function testEmptyStruct(): void
    {
        // Empty struct should still work
        $writer = new WriteBuffer();
        $writer->allocate(0);
        
        $reader = new ReadBuffer($writer->data(), 0, 0);
        $this->assertEquals(0, $reader->size);
    }
    
    public function testStructSizeCalculation(): void
    {
        // Test that struct size is calculated correctly
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        // Write struct: { a: int32, b: int32, c: string }
        $writer->writeInt32(0, 10);
        $writer->writeInt32(4, 20);
        
        $str = "hello";
        $writer->writeInt32(8, strlen($str));
        for ($i = 0; $i < strlen($str); $i++) {
            $writer->writeByte(12 + $i, ord($str[$i]));
        }
        
        $expectedSize = 4 + 4 + 4 + strlen($str); // 17 bytes
        
        $reader = new ReadBuffer($writer->data(), 0, $expectedSize);
        $this->assertEquals($expectedSize, $reader->size);
    }
}

