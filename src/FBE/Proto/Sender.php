<?php

declare(strict_types=1);

namespace FBE\Proto;

use FBE\Common\{WriteBuffer, StructModel};

/**
 * FBE Sender base class for protocol communication
 *
 * Pattern:
 * 1. Application calls send(Model $model)
 * 2. Sender serializes model and prepends type ID + size
 * 3. Sender calls abstract onSend(data, size) for transmission
 *
 * Message format (Final mode):
 * [4-byte size][4-byte type_id][payload]
 *
 * Usage:
 * class MySender extends Sender {
 *     protected function onSend(string $data, int $size): int {
 *         return socket_send($this->socket, $data, $size, 0);
 *     }
 * }
 */
abstract class Sender
{
    protected WriteBuffer $buffer;
    protected int $offset = 0;

    public function __construct()
    {
        // Initialize with reasonable buffer size (can grow)
        $this->buffer = new WriteBuffer(8192);
    }

    /**
     * Send a message (generic struct)
     *
     * @param StructModel $model The model to send
     * @param int $typeId FBE message type ID
     * @return int Bytes sent
     */
    public function send(StructModel $model, int $typeId): int
    {
        // Reset buffer for new message
        $this->buffer->reset();
        $this->buffer->allocate(8192); // Ensure buffer has space
        $this->offset = 0;

        // Reserve space for size (4 bytes) and type_id (4 bytes)
        $headerSize = 8;

        // Get model's serialized data
        $modelBuffer = $model->getBuffer();
        $modelOffset = $model->getOffset();
        $payloadSize = $model->size();

        // Calculate total message size (including header)
        $totalSize = $headerSize + $payloadSize;

        // Write message size at offset 0
        $this->buffer->writeUInt32(0, $totalSize);

        // Write type ID at offset 4
        $this->buffer->writeUInt32(4, $typeId);

        // Copy model data to send buffer (after header)
        for ($i = 0; $i < $payloadSize; $i++) {
            $byte = ord($modelBuffer->data()[$modelOffset + $i]);
            $this->buffer->writeUInt8($headerSize + $i, $byte);
        }

        // Send the serialized data
        return $this->sendSerialized($totalSize);
    }

    /**
     * Send serialized buffer
     *
     * @param int $size Size of data to send
     * @return int Bytes actually sent
     */
    protected function sendSerialized(int $size): int
    {
        if ($size === 0) {
            return 0;
        }

        // Get data from buffer
        $data = substr($this->buffer->data(), 0, $size);

        // Call application's onSend implementation
        $sent = $this->onSend($data, $size);

        return $sent;
    }

    /**
     * Abstract method to send data over transport
     *
     * Implementation should send data to socket, pipe, file, etc.
     *
     * @param string $data Binary data to send
     * @param int $size Size of data
     * @return int Number of bytes actually sent
     */
    abstract protected function onSend(string $data, int $size): int;

    /**
     * Get the send buffer
     */
    public function getBuffer(): WriteBuffer
    {
        return $this->buffer;
    }
}
