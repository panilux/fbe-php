<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding base sender
 */
abstract class Sender
{
    protected WriteBuffer $buffer;
    protected bool $logging = false;

    public function __construct(?WriteBuffer $buffer = null)
    {
        $this->buffer = $buffer ?? new WriteBuffer();
    }

    /**
     * Get the sender buffer
     */
    public function buffer(): WriteBuffer
    {
        return $this->buffer;
    }

    /**
     * Get the logging flag
     */
    public function isLogging(): bool
    {
        return $this->logging;
    }

    /**
     * Enable/Disable logging
     */
    public function setLogging(bool $enable): void
    {
        $this->logging = $enable;
    }

    /**
     * Reset the sender buffer
     */
    public function reset(): void
    {
        $this->buffer = new WriteBuffer();
    }

    /**
     * Send serialized data
     * 
     * @param string $data Serialized binary data
     * @return int Number of bytes sent
     */
    public function sendSerialized(string $data): int
    {
        $size = strlen($data);
        
        if ($this->logging) {
            $this->onSendLog("Sending {$size} bytes");
        }
        
        return $this->onSend($data, $size);
    }

    /**
     * Send a struct
     * 
     * @param object $struct Struct to send
     * @return int Number of bytes sent
     */
    public function send(object $struct): int
    {
        if (!method_exists($struct, 'serialize')) {
            throw new \InvalidArgumentException('Struct must have serialize() method');
        }
        
        $this->buffer = new WriteBuffer();
        $struct->serialize($this->buffer);
        
        if ($this->logging) {
            $structName = get_class($struct);
            $this->onSendLog("Sending struct: {$structName}");
            
            if (method_exists($struct, '__toString')) {
                $this->onSendLog((string)$struct);
            }
        }
        
        return $this->sendSerialized($this->buffer->data());
    }

    /**
     * Send message handler (must be implemented by subclass)
     * 
     * @param string $data Binary data to send
     * @param int $size Size of data
     * @return int Number of bytes actually sent
     */
    abstract protected function onSend(string $data, int $size): int;

    /**
     * Send log message handler (can be overridden)
     * 
     * @param string $message Log message
     */
    protected function onSendLog(string $message): void
    {
        // Default: do nothing, subclass can override
    }
}

