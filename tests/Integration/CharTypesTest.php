<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

final class CharTypesTest extends TestCase
{
    public function testChar(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeChar(0, ord('A'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readChar(0);
        
        $this->assertEquals(ord('A'), $value);
    }
    
    public function testCharDigit(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeChar(0, ord('9'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readChar(0);
        
        $this->assertEquals(ord('9'), $value);
    }
    
    public function testCharSpecial(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(1);
        $writer->writeChar(0, ord('@'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readChar(0);
        
        $this->assertEquals(ord('@'), $value);
    }
    
    public function testWChar(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeWChar(0, mb_ord('Ω'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readWChar(0);
        
        $this->assertEquals(mb_ord('Ω'), $value);
    }
    
    public function testWCharChinese(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeWChar(0, mb_ord('中'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readWChar(0);
        
        $this->assertEquals(mb_ord('中'), $value);
    }
    
    public function testWCharEmoji(): void
    {
        $writer = new WriteBuffer();
        $writer->allocate(4);
        $writer->writeWChar(0, mb_ord('😀'));
        
        $reader = new ReadBuffer($writer->data());
        $value = $reader->readWChar(0);
        
        $this->assertEquals(mb_ord('😀'), $value);
    }
}

