<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding base receiver
 */
abstract class Receiver
{
    protected ReadBuffer $buffer;
    protected bool $logging = false;

    public function __construct()
    {
        $this->buffer = new ReadBuffer('');
    }

    /**
     * Get the receiver buffer
     */
    public function buffer(): ReadBuffer
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
     * Receive and deserialize data
     * 
     * @param string $data Binary data received
     * @return bool True if data was processed successfully
     */
    public function receive(string $data): bool
    {
        $size = strlen($data);
        
        if ($this->logging) {
            $this->onReceiveLog("Received {$size} bytes");
        }
        
        $this->buffer = new ReadBuffer($data);
        
        return $this->onReceive($data, $size);
    }

    /**
     * Receive message handler (must be implemented by subclass)
     * 
     * @param string $data Binary data received
     * @param int $size Size of data
     * @return bool True if processed successfully
     */
    abstract protected function onReceive(string $data, int $size): bool;

    /**
     * Receive log message handler (can be overridden)
     * 
     * @param string $message Log message
     */
    protected function onReceiveLog(string $message): void
    {
        // Default: do nothing, subclass can override
    }
}

