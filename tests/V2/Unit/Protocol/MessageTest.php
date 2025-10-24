<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit\Protocol;

use FBE\V2\Protocol\Messages\{AgentHeartbeat, PanelCommand, CommandResponse};
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testAgentHeartbeatSerialization(): void
    {
        $msg = new AgentHeartbeat();
        $msg->agentId = 123;
        $msg->timestamp = 1234567890123456789;
        $msg->status = 'OK';
        $msg->cpuUsage = 45.5;
        $msg->memoryUsage = 62.3;

        $frame = $msg->toFrame();

        // Frame should have header (8 bytes) + payload
        $this->assertGreaterThan(8, strlen($frame));

        // Parse frame
        $parsed = AgentHeartbeat::parseFrame($frame);

        $this->assertEquals(1001, $parsed['type']);
        $this->assertGreaterThan(0, $parsed['size']);

        // Deserialize
        $deserialized = AgentHeartbeat::deserialize($parsed['payload']);

        $this->assertEquals(123, $deserialized->agentId);
        $this->assertEquals(1234567890123456789, $deserialized->timestamp);
        $this->assertEquals('OK', $deserialized->status);
        $this->assertEqualsWithDelta(45.5, $deserialized->cpuUsage, 0.1);
        $this->assertEqualsWithDelta(62.3, $deserialized->memoryUsage, 0.1);
    }

    public function testPanelCommandSerialization(): void
    {
        $msg = new PanelCommand();
        $msg->commandId = 456;
        $msg->targetAgentId = 789;
        $msg->command = 'restart';
        $msg->parameters = [
            'service' => 'nginx',
            'graceful' => 'true',
        ];
        $msg->timeout = 60;

        $frame = $msg->toFrame();

        $parsed = PanelCommand::parseFrame($frame);
        $this->assertEquals(2001, $parsed['type']);

        $deserialized = PanelCommand::deserialize($parsed['payload']);

        $this->assertEquals(456, $deserialized->commandId);
        $this->assertEquals(789, $deserialized->targetAgentId);
        $this->assertEquals('restart', $deserialized->command);
        $this->assertEquals(60, $deserialized->timeout);
        $this->assertCount(2, $deserialized->parameters);
        $this->assertEquals('nginx', $deserialized->parameters['service']);
        $this->assertEquals('true', $deserialized->parameters['graceful']);
    }

    public function testCommandResponseSerialization(): void
    {
        $msg = new CommandResponse();
        $msg->commandId = 456;
        $msg->agentId = 789;
        $msg->success = true;
        $msg->exitCode = 0;
        $msg->output = 'Service restarted successfully';
        $msg->error = '';
        $msg->executionTime = 1500;

        $frame = $msg->toFrame();

        $parsed = CommandResponse::parseFrame($frame);
        $this->assertEquals(2002, $parsed['type']);

        $deserialized = CommandResponse::deserialize($parsed['payload']);

        $this->assertEquals(456, $deserialized->commandId);
        $this->assertEquals(789, $deserialized->agentId);
        $this->assertTrue($deserialized->success);
        $this->assertEquals(0, $deserialized->exitCode);
        $this->assertEquals('Service restarted successfully', $deserialized->output);
        $this->assertEquals('', $deserialized->error);
        $this->assertEquals(1500, $deserialized->executionTime);
    }

    public function testFrameFormat(): void
    {
        $msg = new AgentHeartbeat();
        $msg->agentId = 1;
        $msg->timestamp = 1000;
        $msg->status = 'OK';

        $frame = $msg->toFrame();

        // Check frame structure: [type:4][size:4][payload:N]
        $this->assertGreaterThanOrEqual(8, strlen($frame));

        $parsed = AgentHeartbeat::parseFrame($frame);

        $this->assertIsInt($parsed['type']);
        $this->assertIsInt($parsed['size']);
        $this->assertIsString($parsed['payload']);
        $this->assertEquals($parsed['size'], strlen($parsed['payload']));
    }

    public function testInvalidFrameTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Frame too short');

        AgentHeartbeat::parseFrame('short');
    }

    public function testInvalidFrameSizeMismatch(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid frame size');

        // Create frame with incorrect size
        $frame = pack('N', 1001) . pack('N', 1000) . 'short';
        AgentHeartbeat::parseFrame($frame);
    }

    public function testEmptyParametersMap(): void
    {
        $msg = new PanelCommand();
        $msg->commandId = 1;
        $msg->targetAgentId = 2;
        $msg->command = 'status';
        $msg->parameters = [];
        $msg->timeout = 10;

        $frame = $msg->toFrame();
        $parsed = PanelCommand::parseFrame($frame);
        $deserialized = PanelCommand::deserialize($parsed['payload']);

        $this->assertEmpty($deserialized->parameters);
    }

    public function testFrameSize(): void
    {
        $msg = new AgentHeartbeat();
        $msg->agentId = 1;
        $msg->timestamp = 1000;
        $msg->status = 'OK';

        $frameSize = $msg->frameSize();
        $actualFrame = $msg->toFrame();

        $this->assertEquals(strlen($actualFrame), $frameSize);
    }
}
