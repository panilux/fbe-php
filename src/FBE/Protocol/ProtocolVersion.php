<?php

declare(strict_types=1);

namespace FBE\Protocol;

/**
 * Protocol version information
 *
 * Follows semantic versioning: MAJOR.MINOR.PATCH
 *
 * - MAJOR: Incompatible protocol changes
 * - MINOR: Backward-compatible new features
 * - PATCH: Backward-compatible bug fixes
 */
class ProtocolVersion
{
    public const CURRENT_MAJOR = 1;
    public const CURRENT_MINOR = 0;
    public const CURRENT_PATCH = 0;

    public function __construct(
        public readonly int $major,
        public readonly int $minor,
        public readonly int $patch
    ) {
        if ($major < 0 || $minor < 0 || $patch < 0) {
            throw new \InvalidArgumentException('Version numbers must be non-negative');
        }
    }

    /**
     * Get current protocol version
     */
    public static function current(): self
    {
        return new self(self::CURRENT_MAJOR, self::CURRENT_MINOR, self::CURRENT_PATCH);
    }

    /**
     * Parse version from string "1.0.0"
     */
    public static function parse(string $version): self
    {
        $parts = explode('.', $version);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Invalid version format (expected: major.minor.patch)');
        }

        return new self(
            (int)$parts[0],
            (int)$parts[1],
            (int)$parts[2]
        );
    }

    /**
     * Check if this version is compatible with another
     *
     * Compatible if: same major version, minor >= other.minor
     */
    public function isCompatible(self $other): bool
    {
        // Major version must match
        if ($this->major !== $other->major) {
            return false;
        }

        // Our version must be >= other version
        if ($this->minor < $other->minor) {
            return false;
        }

        if ($this->minor === $other->minor && $this->patch < $other->patch) {
            return false;
        }

        return true;
    }

    /**
     * Compare versions
     *
     * @return int -1 if this < other, 0 if equal, 1 if this > other
     */
    public function compare(self $other): int
    {
        if ($this->major !== $other->major) {
            return $this->major <=> $other->major;
        }

        if ($this->minor !== $other->minor) {
            return $this->minor <=> $other->minor;
        }

        return $this->patch <=> $other->patch;
    }

    /**
     * Convert to string "1.0.0"
     */
    public function toString(): string
    {
        return "{$this->major}.{$this->minor}.{$this->patch}";
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
