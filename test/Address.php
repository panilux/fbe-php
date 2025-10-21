<?php

namespace FBE\Test;

/**
 * Address data class (readonly, immutable)
 */
readonly class Address
{
    public function __construct(
        public string $city,
        public string $country
    ) {}
}

