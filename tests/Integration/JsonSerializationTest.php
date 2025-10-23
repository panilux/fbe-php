<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBEGenDefaults\Order;

final class JsonSerializationTest extends TestCase
{
    public function testToJson(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $order = new Order();
        $order->id = 12345;
        $order->symbol = 'AAPL';
        $order->price = 150.50;
        $order->volume = 100.0;
        $order->tp = 160.0;
        $order->sl = 140.0;
        
        $json = $order->toJson();
        
        $this->assertIsString($json);
        $this->assertStringContainsString('"id":12345', $json);
        $this->assertStringContainsString('"symbol":"AAPL"', $json);
        $this->assertStringContainsString('"price":150.5', $json);
        $this->assertStringContainsString('"volume":100', $json);
    }
    
    public function testFromJson(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $json = '{"id":67890,"symbol":"GOOGL","price":2800.75,"volume":50.0,"tp":2900.0,"sl":2700.0}';
        
        $order = Order::fromJson($json);
        
        $this->assertEquals(67890, $order->id);
        $this->assertEquals('GOOGL', $order->symbol);
        $this->assertEqualsWithDelta(2800.75, $order->price, 0.01);
        $this->assertEqualsWithDelta(50.0, $order->volume, 0.01);
        $this->assertEqualsWithDelta(2900.0, $order->tp, 0.01);
        $this->assertEqualsWithDelta(2700.0, $order->sl, 0.01);
    }
    
    public function testJsonRoundTrip(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $original = new Order();
        $original->id = 99999;
        $original->symbol = 'TSLA';
        $original->price = 750.25;
        $original->volume = 200.0;
        $original->tp = 800.0;
        $original->sl = 700.0;
        
        $json = $original->toJson();
        $restored = Order::fromJson($json);
        
        $this->assertEquals($original->id, $restored->id);
        $this->assertEquals($original->symbol, $restored->symbol);
        $this->assertEqualsWithDelta($original->price, $restored->price, 0.01);
        $this->assertEqualsWithDelta($original->volume, $restored->volume, 0.01);
        $this->assertEqualsWithDelta($original->tp, $restored->tp, 0.01);
        $this->assertEqualsWithDelta($original->sl, $restored->sl, 0.01);
    }
}

