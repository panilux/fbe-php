<?php

declare(strict_types=1);

namespace FBE;

/**
 * Base interface for FBE field models
 */
interface FieldModel
{
    /**
     * Get FBE type ID
     */
    public function fbeType(): int;

    /**
     * Get FBE offset
     */
    public function fbeOffset(): int;

    /**
     * Get FBE size (fixed size for primitives)
     */
    public function fbeSize(): int;

    /**
     * Get FBE extra size (for variable-length types)
     */
    public function fbeExtra(): int;

    /**
     * Verify field
     */
    public function verify(): bool;
}

