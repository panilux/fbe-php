<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Manager extends Employee
{
    public int $teamSize;

    public function __construct()
    {
        parent::__construct();
        $this->teamSize = 0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = parent::serialize($buffer);
        $buffer->writeInt32($offset, $this->teamSize);
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
        $offset = parent::deserializeFields($buffer);
        $this->teamSize = $buffer->readInt32($offset);
        $offset += 4;
        return $offset;
    }
}
