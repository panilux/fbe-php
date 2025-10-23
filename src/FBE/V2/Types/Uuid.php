<?php

declare(strict_types=1);

namespace FBE\V2\Types;

/**
 * RFC 4122 compliant UUID with big-endian byte ordering
 *
 * FBE Spec: UUID fields use network byte order (big-endian)
 * Format: 16 bytes total
 */
final class Uuid
{
    /**
     * 16-byte binary representation (big-endian network byte order)
     */
    private string $bytes;

    /**
     * Create UUID from string representation
     *
     * @param string $uuid UUID string (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx)
     * @throws \InvalidArgumentException if format is invalid
     */
    public function __construct(string $uuid)
    {
        // Remove hyphens
        $hex = str_replace('-', '', $uuid);

        if (strlen($hex) !== 32) {
            throw new \InvalidArgumentException(
                sprintf('Invalid UUID format: expected 32 hex chars, got %d', strlen($hex))
            );
        }

        if (!ctype_xdigit($hex)) {
            throw new \InvalidArgumentException('UUID must contain only hexadecimal characters');
        }

        // Convert to big-endian bytes (network byte order)
        $this->bytes = '';
        for ($i = 0; $i < 32; $i += 2) {
            $this->bytes .= chr(hexdec(substr($hex, $i, 2)));
        }
    }

    /**
     * Create UUID from 16-byte big-endian binary data
     *
     * @param string $bytes 16-byte binary data
     * @return self
     * @throws \InvalidArgumentException if not exactly 16 bytes
     */
    public static function fromBytes(string $bytes): self
    {
        if (strlen($bytes) !== 16) {
            throw new \InvalidArgumentException(
                sprintf('UUID must be exactly 16 bytes, got %d', strlen($bytes))
            );
        }

        // Convert bytes to UUID string
        $hex = bin2hex($bytes);
        $uuidString = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        return new self($uuidString);
    }

    /**
     * Generate random UUID (version 4)
     *
     * @return self
     */
    public static function random(): self
    {
        $bytes = random_bytes(16);

        // Set version (4) and variant bits (RFC 4122)
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40); // Version 4
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80); // Variant 10xx

        $hex = bin2hex($bytes);
        $uuidString = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        return new self($uuidString);
    }

    /**
     * Get 16-byte binary representation (big-endian)
     *
     * @return string 16 bytes
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }

    /**
     * Get string representation
     *
     * @return string xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
     */
    public function toString(): string
    {
        $hex = bin2hex($this->bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * Get string representation (magic method)
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Check equality with another UUID
     */
    public function equals(Uuid $other): bool
    {
        return $this->bytes === $other->bytes;
    }

    /**
     * Get UUID version (1-5)
     *
     * @return int Version number
     */
    public function version(): int
    {
        return (ord($this->bytes[6]) >> 4) & 0x0F;
    }

    /**
     * Check if UUID is nil (all zeros)
     */
    public function isNil(): bool
    {
        return $this->bytes === str_repeat("\0", 16);
    }
}
