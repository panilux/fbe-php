<?php

declare(strict_types=1);

namespace Proto;

/**
 * State flags
 * 
 * Base type: byte
 * Usage: State::FLAG1 | State::FLAG2
 */
final class State
{
    public const UNKNOWN = 0x00;
    public const INVALID = 0x01;
    public const INITIALIZED = 0x02;
    public const CALCULATED = 0x04;
    public const BROKEN = 0x08;
    public const GOOD = self::INITIALIZED | self::CALCULATED;
    public const BAD = self::UNKNOWN | self::INVALID | self::BROKEN;

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
