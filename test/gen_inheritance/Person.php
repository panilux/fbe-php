<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Person
{
    public string $name;
    public int $age;

    public function __construct()
    {
        $this->name = '';
        $this->age = 0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeString($offset, $this->name);
        $offset += 4 + strlen($this->name);
        $buffer->writeInt32($offset, $this->age);
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
        $this->name = $buffer->readString($offset);
        $offset += 4 + strlen($this->name);
        $this->age = $buffer->readInt32($offset);
        $offset += 4;
        return $offset;
    }
}
