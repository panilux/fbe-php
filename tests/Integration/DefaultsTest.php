<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

require_once __DIR__ . '/../../test/gen_defaults/Config.php';
require_once __DIR__ . '/../../test/gen_defaults/Settings.php';
require_once __DIR__ . '/../../test/gen_defaults/Order.php';

final class DefaultsTest extends TestCase
{
    public function testNumericDefaults(): void
    {
        $config = new \Config();
        
        $this->assertEquals(30, $config->timeout);
        $this->assertEquals(3, $config->retries);
        $this->assertEqualsWithDelta(0.95, $config->threshold, 0.001);
        $this->assertEqualsWithDelta(1.5, $config->ratio, 0.001);
    }
    
    public function testStringAndBooleanDefaults(): void
    {
        $settings = new \Settings();
        
        $this->assertTrue($settings->enabled);
        $this->assertFalse($settings->debug);
        $this->assertEquals("DefaultName", $settings->name);
        $this->assertEquals("/var/log", $settings->path);
    }
    
    public function testMixedDefaults(): void
    {
        $order = new \Order();
        
        $this->assertEquals(0, $order->id);
        $this->assertEquals('', $order->symbol);
        $this->assertEquals(0.0, $order->price);
        $this->assertEquals(0.0, $order->volume);
        $this->assertEquals(10.0, $order->tp);
        $this->assertEquals(-10.0, $order->sl);
    }
    
    public function testSerializationWithDefaults(): void
    {
        $config = new \Config();
        $buffer = new WriteBuffer();
        $size = $config->serialize($buffer);
        
        $this->assertGreaterThan(0, $size);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $config2 = \Config::deserialize($readBuffer);
        
        $this->assertEquals(30, $config2->timeout);
        $this->assertEquals(3, $config2->retries);
        $this->assertEqualsWithDelta(0.95, $config2->threshold, 0.001);
    }
    
    public function testModifyDefaults(): void
    {
        $order = new \Order();
        $order->tp = 20.0;
        $order->sl = -20.0;
        
        $this->assertEquals(20.0, $order->tp);
        $this->assertEquals(-20.0, $order->sl);
    }
    
    public function testDefaultsAfterSerialization(): void
    {
        $settings = new \Settings();
        
        // Serialize with defaults
        $buffer = new WriteBuffer();
        $settings->serialize($buffer);
        
        // Deserialize
        $readBuffer = new ReadBuffer($buffer->data());
        $settings2 = \Settings::deserialize($readBuffer);
        
        // Verify defaults are preserved
        $this->assertTrue($settings2->enabled);
        $this->assertFalse($settings2->debug);
        $this->assertEquals("DefaultName", $settings2->name);
        $this->assertEquals("/var/log", $settings2->path);
    }
    
    public function testOverrideDefaults(): void
    {
        $config = new \Config();
        $config->timeout = 60;
        $config->retries = 5;
        
        $buffer = new WriteBuffer();
        $config->serialize($buffer);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $config2 = \Config::deserialize($readBuffer);
        
        $this->assertEquals(60, $config2->timeout);
        $this->assertEquals(5, $config2->retries);
    }
}

