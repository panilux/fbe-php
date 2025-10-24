<?php

declare(strict_types=1);

namespace FBE\V2\Protocol\Messages;

use FBE\V2\Protocol\Message;
use FBE\V2\Common\{WriteBuffer, ReadBuffer};

/**
 * Panel command message
 *
 * Sent from Panel to Agent to execute commands
 * Agent responds with CommandResponse
 *
 * Message Type: 2001
 */
class PanelCommand extends Message
{
    public int $commandId = 0;
    public int $targetAgentId = 0;
    public string $command = '';
    public array $parameters = []; // Map<String, String>
    public int $timeout = 30; // seconds

    public function type(): int
    {
        return 2001;
    }

    public function serialize(): string
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(1024);

        $buffer->writeInt32(0, $this->commandId);
        $buffer->writeInt32(4, $this->targetAgentId);
        $buffer->writeInt32(8, $this->timeout);

        $offset = 12;
        $offset += $buffer->writeStringInline($offset, $this->command);

        // Write parameters as map (count + key-value pairs)
        $paramCount = count($this->parameters);
        $buffer->writeUInt32($offset, $paramCount);
        $offset += 4;

        foreach ($this->parameters as $key => $value) {
            $offset += $buffer->writeStringInline($offset, (string)$key);
            $offset += $buffer->writeStringInline($offset, (string)$value);
        }

        return $buffer->data();
    }

    public static function deserialize(string $data): static
    {
        $buffer = new ReadBuffer($data);

        $msg = new self();
        $msg->commandId = $buffer->readInt32(0);
        $msg->targetAgentId = $buffer->readInt32(4);
        $msg->timeout = $buffer->readInt32(8);

        $offset = 12;
        [$msg->command, $consumed] = $buffer->readStringInline($offset);
        $offset += $consumed;

        // Read parameters map
        $paramCount = $buffer->readUInt32($offset);
        $offset += 4;

        $msg->parameters = [];
        for ($i = 0; $i < $paramCount; $i++) {
            [$key, $keyConsumed] = $buffer->readStringInline($offset);
            $offset += $keyConsumed;

            [$value, $valueConsumed] = $buffer->readStringInline($offset);
            $offset += $valueConsumed;

            $msg->parameters[$key] = $value;
        }

        return $msg;
    }
}
