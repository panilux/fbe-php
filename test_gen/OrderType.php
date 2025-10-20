<?php

declare(strict_types=1);

enum OrderType: int
{
    case Market = 0;
    case Limit = 1;
    case Stop = 2;
}
