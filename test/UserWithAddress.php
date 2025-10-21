<?php

namespace FBE\Test;

/**
 * User with nested Address struct (readonly, immutable)
 */
readonly class UserWithAddress
{
    public function __construct(
        public int $id,
        public string $name,
        public Address $address  // Nested struct
    ) {}
}

