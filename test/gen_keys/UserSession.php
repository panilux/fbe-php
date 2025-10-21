<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class UserSession
{
    public int $userId;
    public string $sessionId;
    public int $timestamp;
    public string $ipAddress;

    public function __construct()
    {
        $this->userId = 0;
        $this->sessionId = '';
        $this->timestamp = 0;
        $this->ipAddress = '';
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt32($offset, $this->userId);
        $offset += 4;
        $buffer->writeString($offset, $this->sessionId);
        $offset += 4 + strlen($this->sessionId);
        $buffer->writeInt64($offset, $this->timestamp);
        $offset += 8;
        $buffer->writeString($offset, $this->ipAddress);
        $offset += 4 + strlen($this->ipAddress);
        return $offset;
    }

    public static function deserialize(ReadBuffer $buffer): self
    {
        $obj = new self();
        $offset = $obj->deserializeFields($buffer);
        return $obj;
    }

    protected function deserializeFields(ReadBuffer $buffer): int
    {
        $offset = 0;
        $this->userId = $buffer->readInt32($offset);
        $offset += 4;
        $this->sessionId = $buffer->readString($offset);
        $offset += 4 + strlen($this->sessionId);
        $this->timestamp = $buffer->readInt64($offset);
        $offset += 8;
        $this->ipAddress = $buffer->readString($offset);
        $offset += 4 + strlen($this->ipAddress);
        return $offset;
    }

    /**
     * Get key fields for hashing and equality
     */
    public function getKey(): array
    {
        return [$this->userId, $this->sessionId];
    }

    /**
     * Check equality based on key fields
     */
    public function equals(self $other): bool
    {
        if ($this->userId !== $other->userId) {
            return false;
        }
        if ($this->sessionId !== $other->sessionId) {
            return false;
        }
        return true;
    }
}
