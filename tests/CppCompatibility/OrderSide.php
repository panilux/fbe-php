<?php

declare(strict_types=1);

namespace Proto;

/**
 * OrderSide enumeration
 * 
 * Base type: byte
 */
enum OrderSide: int
{
    case Buy = 0;
    case Sell = 1;
}
