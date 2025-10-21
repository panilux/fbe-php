<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Balance
{
    public string $currency;
    public float $amount;

    public function __construct()
    {
        $this->currency = '';
        $this->amount = 0.0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeString($offset, $this->currency);
        $offset += 4 + strlen($this->currency);
        $buffer->writeDouble($offset, $this->amount);
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
        $this->currency = $buffer->readString($offset);
        $offset += 4 + strlen($this->currency);
        $this->amount = $buffer->readDouble($offset);
        $offset += 8;
        return $offset;
    }

    /**
     * Get key fields for hashing and equality
     */
    public function getKey(): array
    {
        return [$this->currency];
    }

    /**
     * Check equality based on key fields
     */
    public function equals(self $other): bool
    {
        if ($this->currency !== $other->currency) {
            return false;
        }
        return true;
    }
}
