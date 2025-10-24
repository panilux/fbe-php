<?php

declare(strict_types=1);

namespace FBE\V2\Protocol;

use FBE\V2\Common\{WriteBuffer, ReadBuffer};

/**
 * Base class for FBE protocol messages
 *
 * Message frame format:
 * [4-byte type][4-byte size][payload]
 *
 * Example usage:
 * ```php
 * class AgentHeartbeat extends Message {
 *     public int $agentId;
 *     public int $timestamp;
 *     public string $status;
 *
 *     public function type(): int { return 1001; }
 *
 *     public function serialize(): string {
 *         $buffer = new WriteBuffer();
 *         $buffer->writeInt32(0, $this->agentId);
 *         $buffer->writeInt64(4, $this->timestamp);
 *         $buffer->writeStringInline(12, $this->status);
 *         return $buffer->data();
 *     }
 *
 *     public static function deserialize(string $data): self {
 *         $buffer = new ReadBuffer($data);
 *         $msg = new self();
 *         $msg->agentId = $buffer->readInt32(0);
 *         $msg->timestamp = $buffer->readInt64(4);
 *         [$msg->status] = $buffer->readStringInline(12);
 *         return $msg;
 *     }
 * }
 * ```
 */
abstract class Message
{
    /**
     * Get message type identifier
     * Each message type must have a unique integer ID
     */
    abstract public function type(): int;

    /**
     * Serialize message payload to binary
     * Should NOT include type or size headers (handled by toFrame())
     */
    abstract public function serialize(): string;

    /**
     * Deserialize message payload from binary
     * Should NOT parse type or size headers (handled by fromFrame())
     *
     * @param string $data Binary payload data
     * @return static Message instance
     */
    abstract public static function deserialize(string $data): static;

    /**
     * Convert message to wire format frame
     *
     * Frame format: [4-byte type][4-byte size][payload]
     *
     * @return string Complete message frame ready for transmission
     */
    public function toFrame(): string
    {
        $payload = $this->serialize();
        $size = strlen($payload);

        $buffer = new WriteBuffer();
        $buffer->allocate(8 + $size);

        // Write header: [type][size]
        $buffer->writeUInt32(0, $this->type());
        $buffer->writeUInt32(4, $size);

        // Write payload
        if ($size > 0) {
            $buffer->writeRawBytes(8, $payload);
        }

        return $buffer->data();
    }

    /**
     * Parse message from wire format frame
     *
     * @param string $frame Complete message frame from network
     * @return array{type: int, size: int, payload: string} Parsed frame components
     */
    public static function parseFrame(string $frame): array
    {
        if (strlen($frame) < 8) {
            throw new \InvalidArgumentException('Frame too short (need at least 8 bytes for header)');
        }

        $buffer = new ReadBuffer($frame);

        $type = $buffer->readUInt32(0);
        $size = $buffer->readUInt32(4);

        // Validate size
        if ($size > strlen($frame) - 8) {
            throw new \InvalidArgumentException(
                "Invalid frame size: expected {$size} bytes payload, got " . (strlen($frame) - 8)
            );
        }

        $payload = substr($frame, 8, $size);

        return [
            'type' => $type,
            'size' => $size,
            'payload' => $payload,
        ];
    }

    /**
     * Get frame size (header + payload)
     */
    public function frameSize(): int
    {
        return 8 + strlen($this->serialize());
    }
}
