<?php

declare(strict_types=1);

namespace Com\Example\Trading;

/**
 * OrderFlags flags
 * 
 * Base type: int32
 * Usage: OrderFlags::FLAG1 | OrderFlags::FLAG2
 */
final class OrderFlags
{
    public const NONE = 0x00;
    public const IOC = 0x01;
    public const GTC = 0x02;
    public const HIDDEN = 0x04;
    public const POST_ONLY = 0x08;

    public static function hasFlag(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }

    public static function setFlag(int $flags, int $flag): int
    {
        return $flags | $flag;
    }

    public static function clearFlag(int $flags, int $flag): int
    {
        return $flags & ~$flag;
    }
}
