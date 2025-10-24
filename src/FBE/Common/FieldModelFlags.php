<?php

declare(strict_types=1);

namespace FBE\Common;

/**
 * Base class for flags field models
 *
 * Flags are bitwise enumerations (fixed-size integers):
 * - Binary: N bytes (underlying type: byte, int32, etc.)
 * - Support bitwise operations: OR, AND, XOR, NOT
 * - Standard vs Final: NO DIFFERENCE (fixed-size, always inline)
 *
 * Example:
 * flags State : byte {
 *     initialized = 0x01;
 *     calculated = 0x02;
 *     invalid = 0x04;
 * }
 *
 * Usage: State::initialized | State::calculated = 0x03
 */
abstract class FieldModelFlags extends FieldModel
{
    /**
     * Size in bytes (depends on underlying type)
     */
    public function size(): int
    {
        return $this->underlyingSize();
    }

    /**
     * Extra data size (always 0 - fixed-size)
     */
    public function extra(): int
    {
        return 0;
    }

    /**
     * Total size
     */
    public function total(): int
    {
        return $this->size();
    }

    /**
     * Get underlying type size in bytes
     * - byte/int8:  1
     * - int16:      2
     * - int32:      4
     * - int64:      8
     */
    abstract protected function underlyingSize(): int;

    /**
     * Read flags value from buffer
     *
     * @return int Flags value (bitwise combination)
     */
    abstract public function get(): int;

    /**
     * Write flags value to buffer
     *
     * @param int $value Flags value (bitwise combination)
     */
    abstract public function set(int $value): void;

    /**
     * Check if flag is set
     *
     * @param int $flags Flags value
     * @param int $flag Single flag to check
     * @return bool True if flag is set
     */
    public static function hasFlag(int $flags, int $flag): bool
    {
        return ($flags & $flag) === $flag;
    }

    /**
     * Set a flag
     *
     * @param int $flags Current flags value
     * @param int $flag Flag to set
     * @return int New flags value
     */
    public static function setFlag(int $flags, int $flag): int
    {
        return $flags | $flag;
    }

    /**
     * Clear a flag
     *
     * @param int $flags Current flags value
     * @param int $flag Flag to clear
     * @return int New flags value
     */
    public static function clearFlag(int $flags, int $flag): int
    {
        return $flags & ~$flag;
    }

    /**
     * Toggle a flag
     *
     * @param int $flags Current flags value
     * @param int $flag Flag to toggle
     * @return int New flags value
     */
    public static function toggleFlag(int $flags, int $flag): int
    {
        return $flags ^ $flag;
    }
}
