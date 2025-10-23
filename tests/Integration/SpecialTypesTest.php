<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class SpecialTypesTest extends TestCase
{
    public function testBytes(): void
    {
        $writer = new WriteBuffer();
        $bytes = pack('C*', 0x01, 0x02, 0x03, 0x04, 0xFF);
        $writer->writeBytes(0, $bytes);
        
        $reader = new ReadBuffer($writer->data());
        $readBytes = $reader->readBytes(0);
        
        $this->assertEquals($bytes, $readBytes);
    }
    
    public function testEmptyBytes(): void
    {
        $writer = new WriteBuffer();
        $bytes = '';
        $writer->writeBytes(0, $bytes);
        
        $reader = new ReadBuffer($writer->data());
        $readBytes = $reader->readBytes(0);
        
        $this->assertEquals($bytes, $readBytes);
    }
    
    public function testDecimal(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $writer->writeDecimal(0, 123456, 3, false); // 123.456
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readDecimal(0);
        $value = $result['value'];
        $scale = $result['scale'];
        $negative = $result['negative'];
        
        $this->assertEquals(123456, $value);
        $this->assertEquals(3, $scale);
        $this->assertFalse($negative);
    }
    
    public function testDecimalNegative(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $writer->writeDecimal(0, 999999, 3, true); // -999.999
        
        $reader = new ReadBuffer($writer->data());
        $result = $reader->readDecimal(0);
        $value = $result['value'];
        $scale = $result['scale'];
        $negative = $result['negative'];
        
        $this->assertEquals(999999, $value);
        $this->assertEquals(3, $scale);
        $this->assertTrue($negative);
    }
    
    public function testUUID(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $uuid = "550e8400-e29b-41d4-a716-446655440000";
        $writer->writeUUID(0, $uuid);
        
        $reader = new ReadBuffer($writer->data());
        $readUuid = $reader->readUUID(0);
        
        $this->assertEquals($uuid, $readUuid);
    }
    
    public function testUUIDZero(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(16);
        $uuid = "00000000-0000-0000-0000-000000000000";
        $writer->writeUUID(0, $uuid);
        
        $reader = new ReadBuffer($writer->data());
        $readUuid = $reader->readUUID(0);
        
        $this->assertEquals($uuid, $readUuid);
    }
    
    public function testTimestamp(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $timestamp = time();
        $writer->writeTimestamp(0, $timestamp);
        
        $reader = new ReadBuffer($writer->data());
        $readTimestamp = $reader->readTimestamp(0);
        
        $this->assertEquals($timestamp, $readTimestamp);
    }
    
    public function testTimestampZero(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $timestamp = 0;
        $writer->writeTimestamp(0, $timestamp);
        
        $reader = new ReadBuffer($writer->data());
        $readTimestamp = $reader->readTimestamp(0);
        
        $this->assertEquals($timestamp, $readTimestamp);
    }
    
    public function testTimestampFuture(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(8);
        $timestamp = 2147483647; // Max 32-bit timestamp
        $writer->writeTimestamp(0, $timestamp);
        
        $reader = new ReadBuffer($writer->data());
        $readTimestamp = $reader->readTimestamp(0);
        
        $this->assertEquals($timestamp, $readTimestamp);
    }
}

