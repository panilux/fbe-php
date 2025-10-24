<?php

declare(strict_types=1);

namespace FBE\Protocol;

use FBE\Common\WriteBuffer;

/**
 * Message sender for FBE protocol
 *
 * Sends messages over a stream (socket, pipe, etc.) with length prefix framing
 *
 * Frame wire format: [4-byte length (big-endian)][message frame]
 *
 * Example usage:
 * ```php
 * $socket = stream_socket_client('tcp://localhost:8080');
 * $sender = new Sender($socket);
 *
 * $message = new AgentHeartbeat();
 * $message->agentId = 123;
 * $message->timestamp = hrtime(true);
 * $message->status = 'OK';
 *
 * $sender->send($message);
 * ```
 */
class Sender
{
    /**
     * @param resource $stream Stream resource (socket, pipe, etc.)
     */
    public function __construct(
        private mixed $stream
    ) {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }
    }

    /**
     * Send a single message
     *
     * @param Message $message Message to send
     * @return int Number of bytes written
     * @throws \RuntimeException If write fails
     */
    public function send(Message $message): int
    {
        $frame = $message->toFrame();
        $length = strlen($frame);

        // Create wire frame: [4-byte length][frame]
        $wireFrame = pack('N', $length) . $frame;

        $written = @fwrite($this->stream, $wireFrame);

        if ($written === false || $written < strlen($wireFrame)) {
            throw new \RuntimeException('Failed to write complete message to stream');
        }

        return $written;
    }

    /**
     * Send multiple messages in batch
     *
     * More efficient than sending individually as it reduces system calls
     *
     * @param Message[] $messages Messages to send
     * @return int Total number of bytes written
     */
    public function sendBatch(array $messages): int
    {
        if (empty($messages)) {
            return 0;
        }

        // Calculate total size needed
        $totalSize = 0;
        $frames = [];

        foreach ($messages as $message) {
            if (!($message instanceof Message)) {
                throw new \InvalidArgumentException('All items must be Message instances');
            }

            $frame = $message->toFrame();
            $frames[] = $frame;
            $totalSize += 4 + strlen($frame); // length prefix + frame
        }

        // Build combined wire frame
        $buffer = new WriteBuffer($totalSize);
        $buffer->allocate($totalSize);
        $offset = 0;

        foreach ($frames as $frame) {
            $length = strlen($frame);
            // Write length prefix (big-endian)
            $buffer->writeRawBytes($offset, pack('N', $length));
            $offset += 4;
            // Write frame
            $buffer->writeRawBytes($offset, $frame);
            $offset += $length;
        }

        // Write combined buffer
        $wireData = $buffer->data();
        $written = @fwrite($this->stream, $wireData);

        if ($written === false || $written < strlen($wireData)) {
            throw new \RuntimeException('Failed to write complete batch to stream');
        }

        return $written;
    }

    /**
     * Flush stream buffer
     */
    public function flush(): bool
    {
        return @fflush($this->stream);
    }

    /**
     * Close the stream
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            @fclose($this->stream);
        }
    }
}
