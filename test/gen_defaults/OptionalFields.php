<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class OptionalFields
{
    public ?int $count;
    public ?string $text;
    public ?bool $flag;
    public ?float $value;

    public function __construct()
    {
        $this->count = null;
        $this->text = "Default";
        $this->flag = true;
        $this->value = 0.0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt32($offset, $this->count);
        $offset += 4;
        $buffer->writeString($offset, $this->text);
        $offset += 4 + strlen($this->text);
        $buffer->writeBool($offset, $this->flag);
        $offset += 1;
        $buffer->writeDouble($offset, $this->value);
        $offset += 8;
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
        $this->count = $buffer->readInt32($offset);
        $offset += 4;
        $this->text = $buffer->readString($offset);
        $offset += 4 + strlen($this->text);
        $this->flag = $buffer->readBool($offset);
        $offset += 1;
        $this->value = $buffer->readDouble($offset);
        $offset += 8;
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
        $obj->count = $data['count'] ?? $obj->count;
        $obj->text = $data['text'] ?? $obj->text;
        $obj->flag = $data['flag'] ?? $obj->flag;
        $obj->value = $data['value'] ?? $obj->value;
        return $obj;
    }

    /**
     * Convert struct to string for logging
     */
    public function __toString(): string
    {
        return 'OptionalFields(' . 'count=' . var_export($this->count, true) . ', ' . 'text=' . var_export($this->text, true) . ', ' . 'flag=' . var_export($this->flag, true) . ', ' . 'value=' . var_export($this->value, true) . ')';
    }

}
