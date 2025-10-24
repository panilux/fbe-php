<?php

declare(strict_types=1);

namespace Com\Example\Trading;

/**
 * OrderSide enumeration
 * 
 * Base type: int32
 */
enum OrderSide: int
{
    case Buy = 0;
    case Sell = 1;
}
