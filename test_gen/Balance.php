<?php

declare(strict_types=1);

class Balance
{
    public ?array $amount;

    public function __construct()
    {
        $this->amount = [];
    }
}
