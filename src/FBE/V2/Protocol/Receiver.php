<?php

declare(strict_types=1);

namespace FBE\V2\Protocol;

/**
 * Message receiver for FBE protocol
 *
 * Receives messages from a stream (socket, pipe, etc.) with length prefix framing
 * Handles partial reads and buffering automatically
 *
 * Frame wire format: [4-byte length (big-endian)][message frame]
 *
 * Example usage:
 * ```php
 * $socket = stream_socket_server('tcp://0.0.0.0:8080');
 * $client = stream_socket_accept($socket);
 * $receiver = new Receiver($client, $registry);
 *
 * while ($message = $receiver->receive()) {
 *     match ($message->type()) {
 *         1001 => handleHeartbeat($message),
 *         1002 => handleStatus($message),
 *         default => logger()->warning("Unknown message type")
 *     };
 * }
 * ```
 */
class Receiver
{
    private string $receiveBuffer = '';
    private const MAX_MESSAGE_SIZE = 10 * 1024 * 1024; // 10 MB

    /**
     * @param resource $stream Stream resource (socket, pipe, etc.)
     * @param MessageRegistry $registry Message type registry for deserialization
     * @param int $readChunkSize Size of read chunks (default: 8KB)
     */
    public function __construct(
        private mixed $stream,
        private MessageRegistry $registry,
        private int $readChunkSize = 8192
    ) {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        if ($readChunkSize <= 0) {
            throw new \InvalidArgumentException('Read chunk size must be positive');
        }
    }

    /**
     * Receive next message from stream
     *
     * Returns null if no complete message is available (non-blocking mode)
     * Blocks until message is available (blocking mode, default for sockets)
     *
     * @return Message|null Deserialized message or null if none available
     * @throws \RuntimeException On stream errors
     * @throws \InvalidArgumentException On invalid message format or unknown type
     */
    public function receive(): ?Message
    {
        // Step 1: Read length prefix (4 bytes)
        while (strlen($this->receiveBuffer) < 4) {
            $data = @fread($this->stream, 4 - strlen($this->receiveBuffer));

            if ($data === false) {
                throw new \RuntimeException('Failed to read from stream');
            }

            if ($data === '') {
                // EOF or no data available (non-blocking)
                if (feof($this->stream)) {
                    return null; // Clean EOF
                }

                // Non-blocking: no data available yet
                return null;
            }

            $this->receiveBuffer .= $data;
        }

        // Step 2: Parse length
        $length = unpack('N', substr($this->receiveBuffer, 0, 4))[1];

        // Validate length
        if ($length > self::MAX_MESSAGE_SIZE) {
            throw new \RuntimeException(
                "Message size too large: {$length} bytes (max: " . self::MAX_MESSAGE_SIZE . ")"
            );
        }

        // Step 3: Read message frame
        $totalNeeded = 4 + $length;

        while (strlen($this->receiveBuffer) < $totalNeeded) {
            $remaining = $totalNeeded - strlen($this->receiveBuffer);
            $toRead = min($remaining, $this->readChunkSize);

            $data = @fread($this->stream, $toRead);

            if ($data === false) {
                throw new \RuntimeException('Failed to read from stream');
            }

            if ($data === '') {
                // EOF or no data available
                if (feof($this->stream)) {
                    throw new \RuntimeException('Unexpected EOF while reading message');
                }

                // Non-blocking: wait for more data
                return null;
            }

            $this->receiveBuffer .= $data;
        }

        // Step 4: Extract frame and update buffer
        $frame = substr($this->receiveBuffer, 4, $length);
        $this->receiveBuffer = substr($this->receiveBuffer, 4 + $length);

        // Step 5: Deserialize message
        return $this->registry->fromFrame($frame);
    }

    /**
     * Check if stream has ended (EOF)
     */
    public function eof(): bool
    {
        return feof($this->stream);
    }

    /**
     * Get current buffer size
     */
    public function bufferSize(): int
    {
        return strlen($this->receiveBuffer);
    }

    /**
     * Clear receive buffer
     */
    public function clearBuffer(): void
    {
        $this->receiveBuffer = '';
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
