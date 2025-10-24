<?php

declare(strict_types=1);

namespace FBE\Types;

use GMP;

/**
 * .NET Decimal type with 96-bit precision
 *
 * FBE Spec: 16-byte decimal format
 * - Bytes 0-11: 96-bit unscaled value (little-endian)
 * - Bytes 12-13: Unused (zero)
 * - Byte 14: Scale (0-28)
 * - Byte 15: Sign (0x00 = positive, 0x80 = negative)
 *
 * Requires: ext-gmp
 */
final class Decimal
{
    /**
     * 96-bit unscaled value
     */
    private GMP $value;

    /**
     * Scale (0-28) - number of decimal places
     */
    private int $scale;

    /**
     * Sign (true = negative, false = positive)
     */
    private bool $negative;

    /**
     * Create decimal from unscaled value and scale
     *
     * @param string|int|GMP $value Unscaled value
     * @param int $scale Scale (0-28)
     * @param bool $negative Sign
     * @throws \InvalidArgumentException if scale is out of range
     */
    public function __construct(string|int|GMP $value, int $scale = 0, bool $negative = false)
    {
        if ($scale < 0 || $scale > 28) {
            throw new \InvalidArgumentException(
                sprintf('Decimal scale must be 0-28, got %d', $scale)
            );
        }

        $this->value = gmp_init($value);
        $this->scale = $scale;
        $this->negative = $negative;
    }

    /**
     * Create decimal from float
     *
     * @param float $number Float value
     * @param int $precision Number of decimal places (default: auto-detect)
     * @return self
     */
    public static function fromFloat(float $number, int $precision = -1): self
    {
        $negative = $number < 0;
        $number = abs($number);

        // Auto-detect precision
        if ($precision < 0) {
            $str = (string)$number;
            if (str_contains($str, '.')) {
                $parts = explode('.', $str);
                $precision = strlen($parts[1]);
            } else {
                $precision = 0;
            }
        }

        // Convert to unscaled value
        $unscaled = (int)($number * pow(10, $precision));

        return new self($unscaled, $precision, $negative);
    }

    /**
     * Create decimal from string
     *
     * @param string $number Decimal string (e.g., "123.45", "-0.001")
     * @return self
     * @throws \InvalidArgumentException if format is invalid
     */
    public static function fromString(string $number): self
    {
        $number = trim($number);

        if (empty($number) || $number === '-' || $number === '+') {
            throw new \InvalidArgumentException('Decimal string cannot be empty');
        }

        $negative = str_starts_with($number, '-');
        $number = ltrim($number, '-+');

        // Handle zero
        if ($number === '0' || $number === '0.0') {
            return new self(0, 0, false);
        }

        // Split into integer and fractional parts
        if (str_contains($number, '.')) {
            [$intPart, $fracPart] = explode('.', $number, 2);
            $scale = strlen($fracPart);
            $unscaled = $intPart . $fracPart;
        } else {
            $scale = 0;
            $unscaled = $number;
        }

        return new self($unscaled, $scale, $negative);
    }

    /**
     * Create decimal from 16-byte binary data
     *
     * @param string $bytes 16-byte binary data
     * @return self
     * @throws \InvalidArgumentException if not exactly 16 bytes
     */
    public static function fromBytes(string $bytes): self
    {
        if (strlen($bytes) !== 16) {
            throw new \InvalidArgumentException(
                sprintf('Decimal must be exactly 16 bytes, got %d', strlen($bytes))
            );
        }

        // Extract 96-bit unscaled value (bytes 0-11, little-endian)
        $low = gmp_import(substr($bytes, 0, 4), 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        $mid = gmp_import(substr($bytes, 4, 4), 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        $high = gmp_import(substr($bytes, 8, 4), 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);

        // Combine: value = low + (mid << 32) + (high << 64)
        $value = gmp_add($low, gmp_mul($mid, gmp_pow(2, 32)));
        $value = gmp_add($value, gmp_mul($high, gmp_pow(2, 64)));

        // Extract scale (byte 14)
        $scale = ord($bytes[14]);

        // Extract sign (byte 15)
        $negative = $bytes[15] === "\x80";

        // Don't pass GMP to constructor again, pass as string
        return new self(gmp_strval($value), $scale, $negative);
    }

    /**
     * Convert to 16-byte binary data
     *
     * @return string 16 bytes
     */
    public function toBytes(): string
    {
        // Split 96-bit value into three 32-bit parts
        $mask32 = gmp_sub(gmp_pow(2, 32), 1);

        $low = gmp_and($this->value, $mask32);
        $mid = gmp_and(gmp_div($this->value, gmp_pow(2, 32)), $mask32);
        $high = gmp_and(gmp_div($this->value, gmp_pow(2, 64)), $mask32);

        // Convert to little-endian bytes
        $bytes = '';
        $bytes .= gmp_export($low, 4, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN) ?: "\x00\x00\x00\x00";
        $bytes .= gmp_export($mid, 4, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN) ?: "\x00\x00\x00\x00";
        $bytes .= gmp_export($high, 4, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN) ?: "\x00\x00\x00\x00";

        // Pad to 12 bytes if needed
        $bytes = str_pad($bytes, 12, "\0", STR_PAD_RIGHT);

        // Bytes 12-13: Unused (zero)
        $bytes .= "\x00\x00";

        // Byte 14: Scale
        $bytes .= chr($this->scale);

        // Byte 15: Sign
        $bytes .= $this->negative ? "\x80" : "\x00";

        return $bytes;
    }

    /**
     * Convert to float
     *
     * @return float
     */
    public function toFloat(): float
    {
        $divisor = gmp_pow(10, $this->scale);
        $result = gmp_div_q($this->value, $divisor, GMP_ROUND_ZERO);
        $floatValue = (float)gmp_strval($result);

        // Add fractional part
        $remainder = gmp_mod($this->value, $divisor);
        $fractional = (float)gmp_strval($remainder) / (float)gmp_strval($divisor);

        return $this->negative ? -($floatValue + $fractional) : ($floatValue + $fractional);
    }

    /**
     * Convert to string
     *
     * @return string Decimal string representation
     */
    public function toString(): string
    {
        if (gmp_cmp($this->value, 0) === 0) {
            return '0';
        }

        $str = gmp_strval($this->value);

        // Add decimal point if needed
        if ($this->scale > 0) {
            $str = str_pad($str, $this->scale + 1, '0', STR_PAD_LEFT);
            $intPart = substr($str, 0, -$this->scale) ?: '0';
            $fracPart = substr($str, -$this->scale);
            $str = $intPart . '.' . $fracPart;

            // Remove trailing zeros
            $str = rtrim($str, '0');
            $str = rtrim($str, '.');
        }

        return $this->negative ? '-' . $str : $str;
    }

    /**
     * String representation (magic method)
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Get unscaled value
     */
    public function getValue(): GMP
    {
        return $this->value;
    }

    /**
     * Get scale
     */
    public function getScale(): int
    {
        return $this->scale;
    }

    /**
     * Check if negative
     */
    public function isNegative(): bool
    {
        return $this->negative;
    }

    /**
     * Check equality with another decimal
     */
    public function equals(Decimal $other): bool
    {
        return gmp_cmp($this->value, $other->value) === 0
            && $this->scale === $other->scale
            && $this->negative === $other->negative;
    }
}
