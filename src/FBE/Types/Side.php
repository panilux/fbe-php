<?php

declare(strict_types=1);

namespace FBE\Types;

/**
 * Example enum for testing
 * Side of a trade (Buy/Sell)
 */
enum Side: int
{
    case Buy = 0;
    case Sell = 1;
}
