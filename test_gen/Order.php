<?php

declare(strict_types=1);

class Order
{
    public ?array $symbol;
    public ?array $side;
    public ?array $type;
    public ?array $price;
    public ?array $volume;

    public function __construct()
    {
        $this->symbol = [];
        $this->side = [];
        $this->type = [];
        $this->price = [];
        $this->volume = [];
    }
}
