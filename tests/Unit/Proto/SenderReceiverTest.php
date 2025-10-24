<?php

declare(strict_types=1);

namespace FBE\Tests\Unit\Proto;

use FBE\Common\{ReadBuffer, WriteBuffer};
use FBE\Proto\{Sender, Receiver};
use FBE\Tests\Unit\Models\PersonFinalModel;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FBE Sender/Receiver protocol
 */
class SenderReceiverTest extends TestCase
{
    public function testSenderReceiverRoundTrip(): void
    {
        $sentData = null;
        $receivedTypeId = null;
        $receivedData = null;

        // Create a test sender
        $sender = new class extends Sender {
            public ?string $lastSent = null;

            protected function onSend(string $data, int $size): int
            {
                $this->lastSent = $data;
                return $size;
            }
        };

        // Create a test receiver
        $receiver = new class extends Receiver {
            public ?int $lastTypeId = null;
            public ?string $lastData = null;

            protected function onReceive(int $typeId, string $data, int $size): void
            {
                $this->lastTypeId = $typeId;
                $this->lastData = $data;
            }
        };

        // Send a message
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $person = new PersonFinalModel($writeBuffer, 8); // Offset 8 for header
        $person->name()->set('Alice');
        $person->age()->set(30);

        $typeId = 1; // PersonMessage type ID
        $sent = $sender->send($person, $typeId);

        // Verify sender sent data
        $this->assertGreaterThan(0, $sent);
        $this->assertNotNull($sender->lastSent);

        // Feed sent data to receiver
        $receiver->receive($sender->lastSent);

        // Verify receiver got the message
        $this->assertEquals($typeId, $receiver->lastTypeId);
        $this->assertNotNull($receiver->lastData);

        // Verify message format
        $msgBuffer = new ReadBuffer($receiver->lastData);
        $msgSize = $msgBuffer->readUInt32(0);
        $msgTypeId = $msgBuffer->readUInt32(4);

        $this->assertGreaterThan(8, $msgSize); // At least header size
        $this->assertEquals($typeId, $msgTypeId);
    }

    public function testReceiverFragmentedData(): void
    {
        $received = [];

        $receiver = new class extends Receiver {
            public array $received = [];

            protected function onReceive(int $typeId, string $data, int $size): void
            {
                $this->received[] = ['typeId' => $typeId, 'size' => $size];
            }
        };

        // Create a complete message manually
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $messageSize = 20;
        $typeId = 42;

        $buffer->writeUInt32(0, $messageSize);
        $buffer->writeUInt32(4, $typeId);
        // Fill rest with dummy data
        for ($i = 8; $i < $messageSize; $i++) {
            $buffer->writeUInt8($i, 0xFF);
        }

        $completeMessage = substr($buffer->data(), 0, $messageSize);

        // Send in fragments
        $fragment1 = substr($completeMessage, 0, 10);
        $fragment2 = substr($completeMessage, 10, 10);

        $receiver->receive($fragment1);
        $this->assertCount(0, $receiver->received); // Not complete yet

        $receiver->receive($fragment2);
        $this->assertCount(1, $receiver->received); // Now complete

        $this->assertEquals($typeId, $receiver->received[0]['typeId']);
        $this->assertEquals($messageSize, $receiver->received[0]['size']);
    }

    public function testReceiverMultipleMessages(): void
    {
        $receiver = new class extends Receiver {
            public array $received = [];

            protected function onReceive(int $typeId, string $data, int $size): void
            {
                $this->received[] = ['typeId' => $typeId, 'size' => $size];
            }
        };

        // Create 3 messages
        $buffer = new WriteBuffer();
        $buffer->allocate(100);

        $offset = 0;
        for ($i = 1; $i <= 3; $i++) {
            $messageSize = 12; // 8 header + 4 payload
            $typeId = $i;

            $buffer->writeUInt32($offset, $messageSize);
            $buffer->writeUInt32($offset + 4, $typeId);
            $buffer->writeUInt32($offset + 8, $i * 100); // Dummy payload

            $offset += $messageSize;
        }

        $allMessages = substr($buffer->data(), 0, $offset);

        // Send all at once
        $receiver->receive($allMessages);

        // Should have received all 3 messages
        $this->assertCount(3, $receiver->received);
        $this->assertEquals(1, $receiver->received[0]['typeId']);
        $this->assertEquals(2, $receiver->received[1]['typeId']);
        $this->assertEquals(3, $receiver->received[2]['typeId']);
    }

    public function testReceiverInvalidMessage(): void
    {
        $receiver = new class extends Receiver {
            public array $received = [];

            protected function onReceive(int $typeId, string $data, int $size): void
            {
                $this->received[] = ['typeId' => $typeId];
            }
        };

        // Send invalid message (size < 8)
        $buffer = new WriteBuffer();
        $buffer->allocate(10);
        $buffer->writeUInt32(0, 4); // Invalid size (< 8)
        $buffer->writeUInt32(4, 99);

        $receiver->receive(substr($buffer->data(), 0, 8));

        // Should not receive anything (buffer cleared)
        $this->assertCount(0, $receiver->received);
        $this->assertEquals(0, $receiver->getBufferSize());
    }

    public function testSenderGetBuffer(): void
    {
        $sender = new class extends Sender {
            protected function onSend(string $data, int $size): int
            {
                return $size;
            }
        };

        $buffer = $sender->getBuffer();
        $this->assertInstanceOf(WriteBuffer::class, $buffer);
    }

    public function testReceiverReset(): void
    {
        $receiver = new class extends Receiver {
            protected function onReceive(int $typeId, string $data, int $size): void
            {
                // Do nothing
            }
        };

        // Add some data
        $receiver->receive('test data');
        $this->assertGreaterThan(0, $receiver->getBufferSize());

        // Reset
        $receiver->reset();
        $this->assertEquals(0, $receiver->getBufferSize());
    }
}
