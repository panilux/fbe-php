<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit\Protocol;

use FBE\V2\Protocol\MessageRegistry;
use FBE\V2\Protocol\Messages\{AgentHeartbeat, PanelCommand, CommandResponse};
use PHPUnit\Framework\TestCase;

class MessageRegistryTest extends TestCase
{
    public function testRegisterMessage(): void
    {
        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);

        $this->assertTrue($registry->has(1001));
        $this->assertEquals(AgentHeartbeat::class, $registry->getClass(1001));
    }

    public function testRegisterMultipleMessages(): void
    {
        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $registry->register(2001, PanelCommand::class);
        $registry->register(2002, CommandResponse::class);

        $this->assertEquals(3, $registry->count());
        $this->assertTrue($registry->has(1001));
        $this->assertTrue($registry->has(2001));
        $this->assertTrue($registry->has(2002));
        $this->assertFalse($registry->has(9999));
    }

    public function testDuplicateTypeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already registered');

        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $registry->register(1001, PanelCommand::class); // Should throw
    }

    public function testInvalidClassThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend');

        $registry = new MessageRegistry();
        $registry->register(1001, \stdClass::class); // Not a Message
    }

    public function testFromFrame(): void
    {
        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);

        // Create message
        $original = new AgentHeartbeat();
        $original->agentId = 123;
        $original->timestamp = 1000;
        $original->status = 'OK';

        // Serialize to frame
        $frame = $original->toFrame();

        // Deserialize via registry
        $deserialized = $registry->fromFrame($frame);

        $this->assertInstanceOf(AgentHeartbeat::class, $deserialized);
        $this->assertEquals(123, $deserialized->agentId);
        $this->assertEquals(1000, $deserialized->timestamp);
        $this->assertEquals('OK', $deserialized->status);
    }

    public function testFromFrameUnknownType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown message type');

        $registry = new MessageRegistry();
        // Don't register type 1001

        $msg = new AgentHeartbeat();
        $msg->agentId = 1;
        $frame = $msg->toFrame();

        $registry->fromFrame($frame); // Should throw
    }

    public function testGetAll(): void
    {
        $registry = new MessageRegistry();
        $registry->register(1001, AgentHeartbeat::class);
        $registry->register(2001, PanelCommand::class);

        $all = $registry->getAll();

        $this->assertCount(2, $all);
        $this->assertEquals(AgentHeartbeat::class, $all[1001]);
        $this->assertEquals(PanelCommand::class, $all[2001]);
    }

    public function testGetClassNonExistent(): void
    {
        $registry = new MessageRegistry();

        $this->assertNull($registry->getClass(9999));
    }
}
