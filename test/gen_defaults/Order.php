<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Order
{
    public int $id;
    public string $symbol;
    public float $price;
    public float $volume;
    public float $tp;
    public float $sl;

    public function __construct()
    {
        $this->id = 0;
        $this->symbol = '';
        $this->price = 0.0;
        $this->volume = 0.0;
        $this->tp = 10.0;
        $this->sl = -10.0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt32($offset, $this->id);
        $offset += 4;
        $buffer->writeString($offset, $this->symbol);
        $offset += 4 + strlen($this->symbol);
        $buffer->writeDouble($offset, $this->price);
        $offset += 8;
        $buffer->writeDouble($offset, $this->volume);
        $offset += 8;
        $buffer->writeDouble($offset, $this->tp);
        $offset += 8;
        $buffer->writeDouble($offset, $this->sl);
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
        $this->id = $buffer->readInt32($offset);
        $offset += 4;
        $this->symbol = $buffer->readString($offset);
        $offset += 4 + strlen($this->symbol);
        $this->price = $buffer->readDouble($offset);
        $offset += 8;
        $this->volume = $buffer->readDouble($offset);
        $offset += 8;
        $this->tp = $buffer->readDouble($offset);
        $offset += 8;
        $this->sl = $buffer->readDouble($offset);
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
        $obj->id = $data['id'] ?? $obj->id;
        $obj->symbol = $data['symbol'] ?? $obj->symbol;
        $obj->price = $data['price'] ?? $obj->price;
        $obj->volume = $data['volume'] ?? $obj->volume;
        $obj->tp = $data['tp'] ?? $obj->tp;
        $obj->sl = $data['sl'] ?? $obj->sl;
        return $obj;
    }

    /**
     * Convert struct to string for logging
     */
    public function __toString(): string
    {
        return 'Order(' . 'id=' . var_export($this->id, true) . ', ' . 'symbol=' . var_export($this->symbol, true) . ', ' . 'price=' . var_export($this->price, true) . ', ' . 'volume=' . var_export($this->volume, true) . ', ' . 'tp=' . var_export($this->tp, true) . ', ' . 'sl=' . var_export($this->sl, true) . ')';
    }

}
