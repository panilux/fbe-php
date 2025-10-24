<?php

declare(strict_types=1);

namespace FBE\Tests\Unit\Protocol;

use FBE\Protocol\{Sender, Receiver, MessageRegistry};
use FBE\Protocol\Messages\{AgentHeartbeat, PanelCommand, CommandResponse};
use PHPUnit\Framework\TestCase;

class SenderReceiverTest extends TestCase
{
    private function createMemoryStream(): array
    {
        // Create a pair of connected memory streams
        $socket = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        return [$socket[0], $socket[1]];
    }

    public function testSendAndReceive(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        // Setup
        $sender = new Sender($clientStream);

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $receiver = new Receiver($serverStream, $registry);

        // Send message
        $msg = new AgentHeartbeat();
        $msg->agentId = 123;
        $msg->timestamp = 1234567890;
        $msg->status = 'OK';
        $msg->cpuUsage = 45.5;
        $msg->memoryUsage = 62.3;

        $sender->send($msg);

        // Receive message
        $received = $receiver->receive();

        $this->assertInstanceOf(AgentHeartbeat::class, $received);
        $this->assertEquals(123, $received->agentId);
        $this->assertEquals(1234567890, $received->timestamp);
        $this->assertEquals('OK', $received->status);
        $this->assertEqualsWithDelta(45.5, $received->cpuUsage, 0.1);
        $this->assertEqualsWithDelta(62.3, $received->memoryUsage, 0.1);

        // Cleanup
        fclose($clientStream);
        fclose($serverStream);
    }

    public function testSendMultipleMessages(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        $sender = new Sender($clientStream);

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $registry->register(2001, PanelCommand::class);
        $receiver = new Receiver($serverStream, $registry);

        // Send heartbeat
        $heartbeat = new AgentHeartbeat();
        $heartbeat->agentId = 1;
        $heartbeat->timestamp = 1000;
        $heartbeat->status = 'OK';
        $sender->send($heartbeat);

        // Send command
        $command = new PanelCommand();
        $command->commandId = 2;
        $command->targetAgentId = 1;
        $command->command = 'status';
        $sender->send($command);

        // Receive both
        $received1 = $receiver->receive();
        $received2 = $receiver->receive();

        $this->assertInstanceOf(AgentHeartbeat::class, $received1);
        $this->assertEquals(1, $received1->agentId);

        $this->assertInstanceOf(PanelCommand::class, $received2);
        $this->assertEquals(2, $received2->commandId);

        fclose($clientStream);
        fclose($serverStream);
    }

    public function testSendBatch(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        $sender = new Sender($clientStream);

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $receiver = new Receiver($serverStream, $registry);

        // Create batch
        $messages = [];
        for ($i = 0; $i < 5; $i++) {
            $msg = new AgentHeartbeat();
            $msg->agentId = $i;
            $msg->timestamp = 1000 + $i;
            $msg->status = 'OK';
            $messages[] = $msg;
        }

        // Send batch
        $sender->sendBatch($messages);

        // Receive all
        $received = [];
        for ($i = 0; $i < 5; $i++) {
            $received[] = $receiver->receive();
        }

        $this->assertCount(5, $received);

        for ($i = 0; $i < 5; $i++) {
            $this->assertInstanceOf(AgentHeartbeat::class, $received[$i]);
            $this->assertEquals($i, $received[$i]->agentId);
            $this->assertEquals(1000 + $i, $received[$i]->timestamp);
        }

        fclose($clientStream);
        fclose($serverStream);
    }

    public function testComplexMessageRoundTrip(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        $sender = new Sender($clientStream);

        $registry = new MessageRegistry();
        $registry->register(2001, PanelCommand::class);
        $receiver = new Receiver($serverStream, $registry);

        // Send complex command
        $command = new PanelCommand();
        $command->commandId = 999;
        $command->targetAgentId = 555;
        $command->command = 'deploy';
        $command->parameters = [
            'app' => 'my-service',
            'version' => '1.2.3',
            'environment' => 'production',
            'region' => 'us-east-1',
        ];
        $command->timeout = 300;

        $sender->send($command);

        $received = $receiver->receive();

        $this->assertInstanceOf(PanelCommand::class, $received);
        $this->assertEquals(999, $received->commandId);
        $this->assertEquals(555, $received->targetAgentId);
        $this->assertEquals('deploy', $received->command);
        $this->assertCount(4, $received->parameters);
        $this->assertEquals('my-service', $received->parameters['app']);
        $this->assertEquals('1.2.3', $received->parameters['version']);
        $this->assertEquals('production', $received->parameters['environment']);
        $this->assertEquals('us-east-1', $received->parameters['region']);
        $this->assertEquals(300, $received->timeout);

        fclose($clientStream);
        fclose($serverStream);
    }

    public function testBufferSize(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $receiver = new Receiver($serverStream, $registry);

        $this->assertEquals(0, $receiver->bufferSize());

        fclose($clientStream);
        fclose($serverStream);
    }

    public function testClearBuffer(): void
    {
        [$clientStream, $serverStream] = $this->createMemoryStream();

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $receiver = new Receiver($serverStream, $registry);

        $receiver->clearBuffer();
        $this->assertEquals(0, $receiver->bufferSize());

        fclose($clientStream);
        fclose($serverStream);
    }
}
