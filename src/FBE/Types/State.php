<?php

declare(strict_types=1);

namespace FBE\Types;

/**
 * State flags (example flags type)
 *
 * FBE Schema:
 * flags State : byte {
 *     initialized = 0x01;
 *     calculated = 0x02;
 *     verified = 0x04;
 *     invalid = 0x08;
 * }
 *
 * Usage:
 * $state = State::INITIALIZED | State::CALCULATED; // 0x03
 * State::hasFlag($state, State::INITIALIZED); // true
 */
final class State
{
    public const NONE = 0x00;
    public const INITIALIZED = 0x01;
    public const CALCULATED = 0x02;
    public const VERIFIED = 0x04;
    public const INVALID = 0x08;

    /**
     * Check if flag is set
     */
    public static function hasFlag(int $state, int $flag): bool
    {
        return ($state & $flag) === $flag;
    }

    /**
     * Set a flag
     */
    public static function setFlag(int $state, int $flag): int
    {
        return $state | $flag;
    }

    /**
     * Clear a flag
     */
    public static function clearFlag(int $state, int $flag): int
    {
        return $state & ~$flag;
    }

    /**
     * Toggle a flag
     */
    public static function toggleFlag(int $state, int $flag): int
    {
        return $state ^ $flag;
    }

    /**
     * Get all set flags as array
     *
     * @return array List of flag names
     */
    public static function getSetFlags(int $state): array
    {
        $flags = [];

        if (self::hasFlag($state, self::INITIALIZED)) {
            $flags[] = 'INITIALIZED';
        }
        if (self::hasFlag($state, self::CALCULATED)) {
            $flags[] = 'CALCULATED';
        }
        if (self::hasFlag($state, self::VERIFIED)) {
            $flags[] = 'VERIFIED';
        }
        if (self::hasFlag($state, self::INVALID)) {
            $flags[] = 'INVALID';
        }

        return $flags;
    }
}
