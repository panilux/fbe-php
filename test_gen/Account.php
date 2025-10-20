<?php

declare(strict_types=1);

class Account
{
    public ?array $name;
    public ?array $state;
    public ?array $wallet;
    public ?array $asset;
    public ?array $orders;

    public function __construct()
    {
        $this->name = [];
        $this->state = [];
        $this->wallet = [];
        $this->asset = [];
        $this->orders = [];
    }
}
