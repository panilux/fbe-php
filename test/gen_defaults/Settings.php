<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Settings
{
    public bool $enabled;
    public bool $debug;
    public string $name;
    public string $path;

    public function __construct()
    {
        $this->enabled = true;
        $this->debug = false;
        $this->name = "DefaultName";
        $this->path = "/var/log";
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeBool($offset, $this->enabled);
        $offset += 1;
        $buffer->writeBool($offset, $this->debug);
        $offset += 1;
        $buffer->writeString($offset, $this->name);
        $offset += 4 + strlen($this->name);
        $buffer->writeString($offset, $this->path);
        $offset += 4 + strlen($this->path);
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
        $this->enabled = $buffer->readBool($offset);
        $offset += 1;
        $this->debug = $buffer->readBool($offset);
        $offset += 1;
        $this->name = $buffer->readString($offset);
        $offset += 4 + strlen($this->name);
        $this->path = $buffer->readString($offset);
        $offset += 4 + strlen($this->path);
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
        $obj->enabled = $data['enabled'] ?? $obj->enabled;
        $obj->debug = $data['debug'] ?? $obj->debug;
        $obj->name = $data['name'] ?? $obj->name;
        $obj->path = $data['path'] ?? $obj->path;
        return $obj;
    }

    /**
     * Convert struct to string for logging
     */
    public function __toString(): string
    {
        return 'Settings(' . 'enabled=' . var_export($this->enabled, true) . ', ' . 'debug=' . var_export($this->debug, true) . ', ' . 'name=' . var_export($this->name, true) . ', ' . 'path=' . var_export($this->path, true) . ')';
    }

}
