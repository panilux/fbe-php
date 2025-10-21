<?php

namespace FBETest;

class Order
{
    public function __construct(
        public int $id = 0,
        public string $symbol = '',
        public int $side = 0,  // 0=buy, 1=sell
        public int $type = 0,  // 0=market, 1=limit, 2=stop
        public float $price = 0.0,
        public float $volume = 0.0
    ) {}
}

