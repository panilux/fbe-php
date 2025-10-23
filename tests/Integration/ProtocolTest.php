<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\Sender;
use FBE\Receiver;
use PHPUnit\Framework\TestCase;

final class ProtocolTest extends TestCase
{
    public function testSenderReceiver(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $sent = null;
        $received = null;
        
        // Create sender
        $sender = new class extends Sender {
            public ?string $lastSent = null;
            
            protected function onSend(string $data, int $size): int
            {
                $this->lastSent = $data;
                return $size;
            }
        };
        
        // Create receiver
        $receiver = new class extends Receiver {
            public ?object $lastReceived = null;
            
            protected function onReceive(string $data, int $size): bool
            {
                $this->lastReceived = \Order::deserialize($this->buffer);
                return true;
            }
        };
        
        // Send order
        $order = new \Order();
        $order->id = 12345;
        $order->symbol = 'AAPL';
        $order->price = 150.50;
        $order->volume = 100.0;
        $order->tp = 160.0;
        $order->sl = 140.0;
        
        $bytesSent = $sender->send($order);
        $this->assertGreaterThan(0, $bytesSent);
        $this->assertNotNull($sender->lastSent);
        
        // Receive order
        $receiver->receive($sender->lastSent);
        $this->assertNotNull($receiver->lastReceived);
        
        $receivedOrder = $receiver->lastReceived;
        $this->assertEquals($order->id, $receivedOrder->id);
        $this->assertEquals($order->symbol, $receivedOrder->symbol);
        $this->assertEqualsWithDelta($order->price, $receivedOrder->price, 0.01);
    }
    
    public function testLogging(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $logs = [];
        
        $sender = new class($logs) extends Sender {
            private array $logs;
            
            public function __construct(array &$logs)
            {
                parent::__construct();
                $this->logs = &$logs;
            }
            
            protected function onSend(string $data, int $size): int
            {
                return $size;
            }
            
            protected function onSendLog(string $message): void
            {
                $this->logs[] = $message;
            }
        };
        
        $sender->setLogging(true);
        $this->assertTrue($sender->isLogging());
        
        $order = new \Order();
        $order->id = 99999;
        $order->symbol = 'TEST';
        $order->price = 100.0;
        $order->volume = 50.0;
        $order->tp = 110.0;
        $order->sl = 90.0;
        
        $sender->send($order);
        
        $this->assertGreaterThan(0, count($logs));
        $this->assertStringContainsString('Sending struct', $logs[0]);
    }
    
    public function testToString(): void
    {
        require_once __DIR__ . '/../../test/gen_defaults/Order.php';
        
        $order = new \Order();
        $order->id = 12345;
        $order->symbol = 'AAPL';
        $order->price = 150.50;
        $order->volume = 100.0;
        $order->tp = 160.0;
        $order->sl = 140.0;
        
        $str = (string)$order;
        
        $this->assertStringContainsString('Order(', $str);
        $this->assertStringContainsString('id=12345', $str);
        $this->assertStringContainsString('symbol=\'AAPL\'', $str);
    }
}

