<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Config
{
    public int $timeout;
    public int $retries;
    public float $threshold;
    public float $ratio;

    public function __construct()
    {
        $this->timeout = 30;
        $this->retries = 3;
        $this->threshold = 0.95;
        $this->ratio = 1.5;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt32($offset, $this->timeout);
        $offset += 4;
        $buffer->writeInt32($offset, $this->retries);
        $offset += 4;
        $buffer->writeDouble($offset, $this->threshold);
        $offset += 8;
        $buffer->writeFloat($offset, $this->ratio);
        $offset += 4;
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
        $this->timeout = $buffer->readInt32($offset);
        $offset += 4;
        $this->retries = $buffer->readInt32($offset);
        $offset += 4;
        $this->threshold = $buffer->readDouble($offset);
        $offset += 8;
        $this->ratio = $buffer->readFloat($offset);
        $offset += 4;
        return $offset;
    }

    /**
     * Convert struct to JSON string
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR);
    }

    /**
     * Create struct from JSON string
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $obj = new self();
        $obj->timeout = $data['timeout'] ?? $obj->timeout;
        $obj->retries = $data['retries'] ?? $obj->retries;
        $obj->threshold = $data['threshold'] ?? $obj->threshold;
        $obj->ratio = $data['ratio'] ?? $obj->ratio;
        return $obj;
    }

}
