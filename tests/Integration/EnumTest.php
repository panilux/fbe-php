<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

// Test enum
enum Color: int
{
    case Red = 0;
    case Green = 1;
    case Blue = 2;
}

enum Status: int
{
    case Pending = 0;
    case Active = 1;
    case Completed = 2;
    case Cancelled = 3;
}

final class EnumTest extends TestCase
{
    public function testEnumSerializeInt32(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        // Write enum as int32
        $writer->writeInt32(0, Color::Red->value);
        $writer->writeInt32(4, Color::Green->value);
        
        $reader = new ReadBuffer($writer->data(), 0, 8);
        
        $red = Color::from($reader->readInt32(0));
        $green = Color::from($reader->readInt32(4));
        
        $this->assertEquals(Color::Red, $red);
        $this->assertEquals(Color::Green, $green);
    }
    
    public function testEnumAllValues(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(20);
        
        $writer->writeInt32(0, Color::Red->value);
        $writer->writeInt32(4, Color::Green->value);
        $writer->writeInt32(8, Color::Blue->value);
        
        $reader = new ReadBuffer($writer->data(), 0, 12);
        
        $this->assertEquals(0, $reader->readInt32(0));
        $this->assertEquals(1, $reader->readInt32(4));
        $this->assertEquals(2, $reader->readInt32(8));
    }
    
    public function testEnumInStruct(): void
    {
        // Struct: { id: 42, status: Active, name: "test" }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $writer->writeInt32(0, 42); // id
        $writer->writeInt32(4, Status::Active->value); // status enum
        $writer->writeString(8, "test"); // name
        
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $id = $reader->readInt32(0);
        $status = Status::from($reader->readInt32(4));
        $name = $reader->readString(8);
        
        $this->assertEquals(42, $id);
        $this->assertEquals(Status::Active, $status);
        $this->assertEquals("test", $name);
    }
    
    public function testEnumArray(): void
    {
        // Array of enums
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $enums = [Status::Pending, Status::Active, Status::Completed];
        
        // Write array length
        $writer->writeInt32(0, count($enums));
        
        // Write enum values
        for ($i = 0; $i < count($enums); $i++) {
            $writer->writeInt32(4 + $i * 4, $enums[$i]->value);
        }
        
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $length = $reader->readInt32(0);
        $this->assertEquals(3, $length);
        
        $readEnums = [];
        for ($i = 0; $i < $length; $i++) {
            $readEnums[] = Status::from($reader->readInt32(4 + $i * 4));
        }
        
        $this->assertEquals($enums, $readEnums);
    }
    
    public function testEnumDefaultValue(): void
    {
        // Test enum with default value (first value)
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        // Don't write anything, just read default (0)
        $writer->writeInt32(0, 0);
        
        $reader = new ReadBuffer($writer->data(), 0, 4);
        $color = Color::from($reader->readInt32(0));
        
        $this->assertEquals(Color::Red, $color);
    }
    
    public function testMultipleEnumTypes(): void
    {
        // Test multiple enum types in same buffer
        $writer = new WriteBuffer();
        $writer->allocate(20);
        
        $writer->writeInt32(0, Color::Blue->value);
        $writer->writeInt32(4, Status::Completed->value);
        $writer->writeInt32(8, Color::Green->value);
        
        $reader = new ReadBuffer($writer->data(), 0, 12);
        
        $color1 = Color::from($reader->readInt32(0));
        $status = Status::from($reader->readInt32(4));
        $color2 = Color::from($reader->readInt32(8));
        
        $this->assertEquals(Color::Blue, $color1);
        $this->assertEquals(Status::Completed, $status);
        $this->assertEquals(Color::Green, $color2);
    }
}

