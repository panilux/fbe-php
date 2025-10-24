<?php

declare(strict_types=1);

namespace Com\Example\Trading;

/**
 * OrderType enumeration
 * 
 * Base type: int32
 */
enum OrderType: int
{
    case Market = 0;
    case Limit = 1;
    case Stop = 2;
    case StopLimit = 3;
}
