<?php

declare(strict_types=1);

namespace FBE\Test;

/**
 * User data class (PHP 8.4+)
 *
 * Immutable data class using readonly properties.
 */
final readonly class User
{
    public function __construct(
        public int $id,
        public string $name,
        public Side $side,
    ) {}
}

