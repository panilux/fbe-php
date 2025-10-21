<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Order
{
    public int $id;
    public string $symbol;
    public float $price;

    public function __construct()
    {
        $this->id = 0;
        $this->symbol = '';
        $this->price = 0.0;
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
        return $offset;
    }

    /**
     * Get key fields for hashing and equality
     */
    public function getKey(): array
    {
        return [$this->id];
    }

    /**
     * Check equality based on key fields
     */
    public function equals(self $other): bool
    {
        if ($this->id !== $other->id) {
            return false;
        }
        return true;
    }
}
