<?php

declare(strict_types=1);

namespace FBE\V2\Types;

/**
 * Order status enum (int8 underlying type)
 */
enum OrderStatus: int
{
    case Pending = 0;
    case Processing = 1;
    case Shipped = 2;
    case Delivered = 3;
    case Cancelled = 4;
    case Refunded = 5;
}
