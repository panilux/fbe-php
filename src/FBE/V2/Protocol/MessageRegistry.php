<?php

declare(strict_types=1);

namespace FBE\V2\Protocol;

/**
 * Message type registry for deserializing messages by type ID
 *
 * Usage:
 * ```php
 * $registry = new MessageRegistry();
 * $registry->register(1001, AgentHeartbeat::class);
 * $registry->register(1002, AgentStatus::class);
 *
 * // Deserialize from frame
 * $message = $registry->fromFrame($frame);
 * ```
 */
class MessageRegistry
{
    /**
     * @var array<int, class-string<Message>> Map of type ID to message class
     */
    private array $messageTypes = [];

    /**
     * Register a message type
     *
     * @param int $type Message type identifier
     * @param class-string<Message> $className Fully-qualified message class name
     */
    public function register(int $type, string $className): void
    {
        if (!is_subclass_of($className, Message::class)) {
            throw new \InvalidArgumentException(
                "Class {$className} must extend " . Message::class
            );
        }

        if (isset($this->messageTypes[$type])) {
            throw new \InvalidArgumentException(
                "Message type {$type} already registered as {$this->messageTypes[$type]}"
            );
        }

        $this->messageTypes[$type] = $className;
    }

    /**
     * Check if a message type is registered
     */
    public function has(int $type): bool
    {
        return isset($this->messageTypes[$type]);
    }

    /**
     * Get message class for a type
     *
     * @return class-string<Message>|null
     */
    public function getClass(int $type): ?string
    {
        return $this->messageTypes[$type] ?? null;
    }

    /**
     * Deserialize message from frame
     *
     * @param string $frame Complete message frame
     * @return Message Deserialized message instance
     * @throws \InvalidArgumentException If message type is not registered or frame is invalid
     */
    public function fromFrame(string $frame): Message
    {
        $parsed = Message::parseFrame($frame);

        $type = $parsed['type'];
        $payload = $parsed['payload'];

        if (!isset($this->messageTypes[$type])) {
            throw new \InvalidArgumentException("Unknown message type: {$type}");
        }

        $className = $this->messageTypes[$type];

        return $className::deserialize($payload);
    }

    /**
     * Get all registered message types
     *
     * @return array<int, class-string<Message>>
     */
    public function getAll(): array
    {
        return $this->messageTypes;
    }

    /**
     * Get count of registered message types
     */
    public function count(): int
    {
        return count($this->messageTypes);
    }
}
