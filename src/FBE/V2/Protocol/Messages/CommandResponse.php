<?php

declare(strict_types=1);

namespace FBE\V2\Protocol\Messages;

use FBE\V2\Protocol\Message;
use FBE\V2\Common\{WriteBuffer, ReadBuffer};

/**
 * Command response message
 *
 * Sent from Agent to Panel after executing a command
 * Contains command result, output, and status
 *
 * Message Type: 2002
 */
class CommandResponse extends Message
{
    public int $commandId = 0;
    public int $agentId = 0;
    public bool $success = false;
    public int $exitCode = 0;
    public string $output = '';
    public string $error = '';
    public int $executionTime = 0; // milliseconds

    public function type(): int
    {
        return 2002;
    }

    public function serialize(): string
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(2048);

        $buffer->writeInt32(0, $this->commandId);
        $buffer->writeInt32(4, $this->agentId);
        $buffer->writeBool(8, $this->success);
        $buffer->writeInt32(9, $this->exitCode);
        $buffer->writeInt32(13, $this->executionTime);

        $offset = 17;
        $offset += $buffer->writeStringInline($offset, $this->output);
        $offset += $buffer->writeStringInline($offset, $this->error);

        return $buffer->data();
    }

    public static function deserialize(string $data): static
    {
        $buffer = new ReadBuffer($data);

        $msg = new self();
        $msg->commandId = $buffer->readInt32(0);
        $msg->agentId = $buffer->readInt32(4);
        $msg->success = $buffer->readBool(8);
        $msg->exitCode = $buffer->readInt32(9);
        $msg->executionTime = $buffer->readInt32(13);

        $offset = 17;
        [$msg->output, $consumed1] = $buffer->readStringInline($offset);
        $offset += $consumed1;
        [$msg->error, $consumed2] = $buffer->readStringInline($offset);

        return $msg;
    }
}
