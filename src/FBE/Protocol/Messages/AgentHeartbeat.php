<?php

declare(strict_types=1);

namespace FBE\Protocol\Messages;

use FBE\Protocol\Message;
use FBE\Common\{WriteBuffer, ReadBuffer};

/**
 * Agent heartbeat message
 *
 * Sent periodically by agents to indicate they are alive
 * Panel uses this to detect disconnected agents
 *
 * Message Type: 1001
 */
class AgentHeartbeat extends Message
{
    public int $agentId = 0;
    public int $timestamp = 0; // Nanoseconds since epoch
    public string $status = 'OK'; // OK, WARNING, ERROR
    public float $cpuUsage = 0.0;
    public float $memoryUsage = 0.0;

    public function type(): int
    {
        return 1001;
    }

    public function serialize(): string
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(256); // Reasonable initial size

        $buffer->writeInt32(0, $this->agentId);
        $buffer->writeInt64(4, $this->timestamp);
        $buffer->writeFloat(12, $this->cpuUsage);
        $buffer->writeFloat(16, $this->memoryUsage);
        $buffer->writeStringInline(20, $this->status);

        return $buffer->data();
    }

    public static function deserialize(string $data): static
    {
        $buffer = new ReadBuffer($data);

        $msg = new self();
        $msg->agentId = $buffer->readInt32(0);
        $msg->timestamp = $buffer->readInt64(4);
        $msg->cpuUsage = $buffer->readFloat(12);
        $msg->memoryUsage = $buffer->readFloat(16);
        [$msg->status] = $buffer->readStringInline(20);

        return $msg;
    }
}
