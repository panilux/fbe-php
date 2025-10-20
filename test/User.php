<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class User
{
    public int $id;
    public string $name;
    public Side $side;

    public function __construct()
    {
        $this->id = 0;
        $this->name = '';
        $this->side = null;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = 0;
        $buffer->writeInt32($offset, $this->id);
        $offset += 4;
        $buffer->writeString($offset, $this->name);
        $offset += 4 + strlen($this->name);
        $buffer->writeInt32($offset, $this->side);
        $offset += 4;
        return $offset;
    }

    public static function deserialize(ReadBuffer $buffer): self
    {
        $obj = new self();
        $offset = 0;
        $obj->id = $buffer->readInt32($offset);
        $offset += 4;
        $obj->name = $buffer->readString($offset);
        $offset += 4 + strlen($obj->name);
        $obj->side = $buffer->readInt32($offset);
        $offset += 4;
        return $obj;
    }
}
