<?php

declare(strict_types=1);

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Employee extends Person
{
    public string $company;
    public float $salary;

    public function __construct()
    {
        parent::__construct();
        $this->company = '';
        $this->salary = 0.0;
    }

    public function serialize(WriteBuffer $buffer): int
    {
        $offset = parent::serialize($buffer);
        $buffer->writeString($offset, $this->company);
        $offset += 4 + strlen($this->company);
        $buffer->writeDouble($offset, $this->salary);
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
        $offset = parent::deserializeFields($buffer);
        $this->company = $buffer->readString($offset);
        $offset += 4 + strlen($this->company);
        $this->salary = $buffer->readDouble($offset);
        $offset += 8;
        return $offset;
    }
}
