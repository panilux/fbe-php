<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBEGenKeys\Order as KeysOrder;
use FBEGenKeys\Balance;
use FBEGenKeys\UserSession;
use FBEGenKeys\LogEntry;

require_once __DIR__ . '/../../test/gen_keys/Order.php';
require_once __DIR__ . '/../../test/gen_keys/Balance.php';
require_once __DIR__ . '/../../test/gen_keys/UserSession.php';
require_once __DIR__ . '/../../test/gen_keys/LogEntry.php';

final class KeysTest extends TestCase
{
    public function testSingleKeyField(): void
    {
        $order1 = new KeysOrder();
        $order1->id = 123;
        $order1->symbol = "AAPL";
        $order1->price = 150.50;
        
        $order2 = new KeysOrder();
        $order2->id = 123;
        $order2->symbol = "GOOGL";
        $order2->price = 200.00;
        
        $order3 = new KeysOrder();
        $order3->id = 456;
        $order3->symbol = "AAPL";
        $order3->price = 150.50;
        
        $this->assertTrue($order1->equals($order2), "Same id should be equal");
        $this->assertFalse($order1->equals($order3), "Different id should not be equal");
    }
    
    public function testStringKeyField(): void
    {
        $balance1 = new Balance();
        $balance1->currency = "USD";
        $balance1->amount = 1000.00;
        
        $balance2 = new Balance();
        $balance2->currency = "USD";
        $balance2->amount = 2000.00;
        
        $balance3 = new Balance();
        $balance3->currency = "EUR";
        $balance3->amount = 1000.00;
        
        $this->assertTrue($balance1->equals($balance2), "Same currency should be equal");
        $this->assertFalse($balance1->equals($balance3), "Different currency should not be equal");
    }
    
    public function testCompositeKey(): void
    {
        $session1 = new UserSession();
        $session1->userId = 100;
        $session1->sessionId = "abc123";
        $session1->timestamp = 1234567890;
        $session1->ipAddress = "192.168.1.1";
        
        $session2 = new UserSession();
        $session2->userId = 100;
        $session2->sessionId = "abc123";
        $session2->timestamp = 9876543210;
        $session2->ipAddress = "10.0.0.1";
        
        $session3 = new UserSession();
        $session3->userId = 100;
        $session3->sessionId = "xyz789";
        $session3->timestamp = 1234567890;
        $session3->ipAddress = "192.168.1.1";
        
        $session4 = new UserSession();
        $session4->userId = 200;
        $session4->sessionId = "abc123";
        $session4->timestamp = 1234567890;
        $session4->ipAddress = "192.168.1.1";
        
        $this->assertTrue($session1->equals($session2), "Same userId+sessionId should be equal");
        $this->assertFalse($session1->equals($session3), "Different sessionId should not be equal");
        $this->assertFalse($session1->equals($session4), "Different userId should not be equal");
    }
    
    public function testNoKeyFields(): void
    {
        $log = new LogEntry();
        $log->timestamp = 1234567890;
        $log->message = "Test message";
        $log->level = "INFO";
        
        $this->assertFalse(method_exists($log, 'getKey'));
        $this->assertFalse(method_exists($log, 'equals'));
    }
    
    public function testHashMapUsage(): void
    {
        $orderMap = [];
        
        $o1 = new KeysOrder();
        $o1->id = 1;
        $o1->symbol = "AAPL";
        $o1->price = 150.00;
        
        $o2 = new KeysOrder();
        $o2->id = 2;
        $o2->symbol = "GOOGL";
        $o2->price = 200.00;
        
        // Use id directly as key (getKey returns array)
        $orderMap[$o1->id] = $o1;
        $orderMap[$o2->id] = $o2;
        
        $this->assertCount(2, $orderMap);
        $this->assertArrayHasKey(1, $orderMap);
        $this->assertArrayHasKey(2, $orderMap);
        $this->assertEquals("AAPL", $orderMap[1]->symbol);
        $this->assertEquals("GOOGL", $orderMap[2]->symbol);
    }
    
    public function testKeySerialization(): void
    {
        $order = new KeysOrder();
        $order->id = 999;
        $order->symbol = "TSLA";
        $order->price = 300.00;
        
        $buffer = new WriteBuffer();
        $order->serialize($buffer);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $order2 = KeysOrder::deserialize($readBuffer);
        
        $this->assertTrue($order->equals($order2));
        $this->assertEquals($order->getKey(), $order2->getKey());
    }
}

