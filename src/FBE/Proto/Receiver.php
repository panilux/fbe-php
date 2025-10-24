<?php

declare(strict_types=1);

namespace FBE\Proto;

use FBE\Common\ReadBuffer;

/**
 * FBE Receiver base class for protocol communication
 *
 * Pattern:
 * 1. Application feeds received bytes via receive(data)
 * 2. Receiver accumulates data and extracts complete messages
 * 3. For each complete message, calls onReceive(type_id, data, size)
 * 4. Application implements onReceive() to dispatch by type_id
 *
 * Message format (Final mode):
 * [4-byte size][4-byte type_id][payload]
 *
 * Usage:
 * class MyReceiver extends Receiver {
 *     protected function onReceive(int $typeId, string $data, int $size): void {
 *         switch ($typeId) {
 *             case 1: // AccountMessage
 *                 $account = AccountFinalModel::deserialize($data);
 *                 $this->handleAccount($account);
 *                 break;
 *         }
 *     }
 * }
 */
abstract class Receiver
{
    protected string $buffer = '';
    protected int $bufferSize = 0;

    /**
     * Receive incoming data
     *
     * Accumulates data and processes complete messages
     *
     * @param string $data Incoming binary data
     * @param int $size Size of incoming data (default: strlen($data))
     */
    public function receive(string $data, int $size = 0): void
    {
        if ($size === 0) {
            $size = strlen($data);
        }

        if ($size === 0) {
            return;
        }

        // Append to buffer
        $this->buffer .= substr($data, 0, $size);
        $this->bufferSize += $size;

        // Process complete messages
        $this->processMessages();
    }

    /**
     * Process accumulated messages
     */
    protected function processMessages(): void
    {
        while ($this->bufferSize >= 8) { // Minimum: 4-byte size + 4-byte type_id
            // Read message size (first 4 bytes)
            $readBuffer = new ReadBuffer($this->buffer);
            $messageSize = $readBuffer->readUInt32(0);

            // Validate message size
            if ($messageSize < 8) {
                // Invalid message - clear buffer
                $this->buffer = '';
                $this->bufferSize = 0;
                return;
            }

            // Check if we have complete message
            if ($this->bufferSize < $messageSize) {
                // Wait for more data
                return;
            }

            // Extract type ID (next 4 bytes)
            $typeId = $readBuffer->readUInt32(4);

            // Extract complete message
            $message = substr($this->buffer, 0, $messageSize);

            // Call application's onReceive handler
            $this->onReceive($typeId, $message, $messageSize);

            // Remove processed message from buffer
            $this->buffer = substr($this->buffer, $messageSize);
            $this->bufferSize -= $messageSize;
        }
    }

    /**
     * Abstract method to handle received messages
     *
     * Implementation should dispatch based on type ID
     *
     * @param int $typeId FBE message type ID
     * @param string $data Complete message data (including header)
     * @param int $size Message size
     */
    abstract protected function onReceive(int $typeId, string $data, int $size): void;

    /**
     * Get current buffer size (for debugging)
     */
    public function getBufferSize(): int
    {
        return $this->bufferSize;
    }

    /**
     * Clear the receive buffer
     */
    public function reset(): void
    {
        $this->buffer = '';
        $this->bufferSize = 0;
    }
}
