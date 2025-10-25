<?php

declare(strict_types=1);

namespace Proto;

/**
 * OrderType enumeration
 * 
 * Base type: byte
 */
enum OrderType: int
{
    case Market = 0;
    case Limit = 1;
    case Stop = 2;
}
