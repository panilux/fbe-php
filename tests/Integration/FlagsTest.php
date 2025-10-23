<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

// Test flags (bitfields)
final class Permissions
{
    public const NONE = 0x00;
    public const READ = 0x01;
    public const WRITE = 0x02;
    public const EXECUTE = 0x04;
    public const DELETE = 0x08;
    public const ALL = self::READ | self::WRITE | self::EXECUTE | self::DELETE;
}

final class FileFlags
{
    public const NONE = 0x00;
    public const HIDDEN = 0x01;
    public const SYSTEM = 0x02;
    public const ARCHIVE = 0x04;
    public const READONLY = 0x08;
    public const COMPRESSED = 0x10;
}

final class FlagsTest extends TestCase
{
    public function testFlagsSingleBit(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeInt32(0, Permissions::READ);
        $writer->writeInt32(4, Permissions::WRITE);
        
        $reader = new ReadBuffer($writer->data(), 0, 8);
        
        $this->assertEquals(Permissions::READ, $reader->readInt32(0));
        $this->assertEquals(Permissions::WRITE, $reader->readInt32(4));
    }
    
    public function testFlagsCombined(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $flags = Permissions::READ | Permissions::WRITE;
        $writer->writeInt32(0, $flags);
        
        $reader = new ReadBuffer($writer->data(), 0, 4);
        $readFlags = $reader->readInt32(0);
        
        $this->assertEquals(0x03, $readFlags);
        $this->assertTrue(($readFlags & Permissions::READ) !== 0);
        $this->assertTrue(($readFlags & Permissions::WRITE) !== 0);
        $this->assertFalse(($readFlags & Permissions::EXECUTE) !== 0);
    }
    
    public function testFlagsAll(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeInt32(0, Permissions::ALL);
        
        $reader = new ReadBuffer($writer->data(), 0, 4);
        $flags = $reader->readInt32(0);
        
        $this->assertTrue(($flags & Permissions::READ) !== 0);
        $this->assertTrue(($flags & Permissions::WRITE) !== 0);
        $this->assertTrue(($flags & Permissions::EXECUTE) !== 0);
        $this->assertTrue(($flags & Permissions::DELETE) !== 0);
    }
    
    public function testFlagsNone(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(10);
        
        $writer->writeInt32(0, Permissions::NONE);
        
        $reader = new ReadBuffer($writer->data(), 0, 4);
        $flags = $reader->readInt32(0);
        
        $this->assertEquals(0, $flags);
        $this->assertFalse(($flags & Permissions::READ) !== 0);
        $this->assertFalse(($flags & Permissions::WRITE) !== 0);
    }
    
    public function testFlagsInStruct(): void
    {
        // Struct: { id: 42, permissions: READ|WRITE, name: "file" }
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $writer->writeInt32(0, 42);
        $writer->writeInt32(4, Permissions::READ | Permissions::WRITE);
        $writer->writeString(8, "file");
        
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $id = $reader->readInt32(0);
        $permissions = $reader->readInt32(4);
        $name = $reader->readString(8);
        
        $this->assertEquals(42, $id);
        $this->assertTrue(($permissions & Permissions::READ) !== 0);
        $this->assertTrue(($permissions & Permissions::WRITE) !== 0);
        $this->assertEquals("file", $name);
    }
    
    public function testMultipleFlagsTypes(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(20);
        
        $writer->writeInt32(0, Permissions::READ | Permissions::EXECUTE);
        $writer->writeInt32(4, FileFlags::HIDDEN | FileFlags::READONLY);
        
        $reader = new ReadBuffer($writer->data(), 0, 8);
        
        $perms = $reader->readInt32(0);
        $fileFlags = $reader->readInt32(4);
        
        $this->assertTrue(($perms & Permissions::READ) !== 0);
        $this->assertTrue(($perms & Permissions::EXECUTE) !== 0);
        $this->assertFalse(($perms & Permissions::WRITE) !== 0);
        
        $this->assertTrue(($fileFlags & FileFlags::HIDDEN) !== 0);
        $this->assertTrue(($fileFlags & FileFlags::READONLY) !== 0);
        $this->assertFalse(($fileFlags & FileFlags::ARCHIVE) !== 0);
    }
    
    public function testFlagsOperations(): void
    {
        // Test bitwise operations
        $flags = Permissions::NONE;
        
        // Add READ
        $flags |= Permissions::READ;
        $this->assertTrue(($flags & Permissions::READ) !== 0);
        
        // Add WRITE
        $flags |= Permissions::WRITE;
        $this->assertTrue(($flags & Permissions::WRITE) !== 0);
        
        // Remove READ
        $flags &= ~Permissions::READ;
        $this->assertFalse(($flags & Permissions::READ) !== 0);
        $this->assertTrue(($flags & Permissions::WRITE) !== 0);
        
        // Write and read
        $writer = new WriteBuffer();
        $writer->allocate(10);
        $writer->writeInt32(0, $flags);
        
        $reader = new ReadBuffer($writer->data(), 0, 4);
        $readFlags = $reader->readInt32(0);
        
        $this->assertEquals($flags, $readFlags);
    }
    
    public function testFlagsArray(): void
    {
        // Array of flags
        $writer = new WriteBuffer();
        $writer->allocate(100);
        
        $flagsArray = [
            Permissions::READ,
            Permissions::READ | Permissions::WRITE,
            Permissions::ALL,
            Permissions::NONE
        ];
        
        $writer->writeInt32(0, count($flagsArray));
        for ($i = 0; $i < count($flagsArray); $i++) {
            $writer->writeInt32(4 + $i * 4, $flagsArray[$i]);
        }
        
        $reader = new ReadBuffer($writer->data(), 0, 100);
        
        $length = $reader->readInt32(0);
        $this->assertEquals(4, $length);
        
        for ($i = 0; $i < $length; $i++) {
            $flags = $reader->readInt32(4 + $i * 4);
            $this->assertEquals($flagsArray[$i], $flags);
        }
    }
}

