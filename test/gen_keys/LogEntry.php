<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class LogEntry
{
    public int $timestamp;
    public string $message;
    public string $level;

    public function __construct()
    {
        $this->timestamp = 0;
        $this->message = '';
        $this->level = '';
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt64($offset, $this->timestamp);
        $offset += 8;
        $buffer->writeString($offset, $this->message);
        $offset += 4 + strlen($this->message);
        $buffer->writeString($offset, $this->level);
        $offset += 4 + strlen($this->level);
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
        $this->timestamp = $buffer->readInt64($offset);
        $offset += 8;
        $this->message = $buffer->readString($offset);
        $offset += 4 + strlen($this->message);
        $this->level = $buffer->readString($offset);
        $offset += 4 + strlen($this->level);
        return $offset;
    }

}
