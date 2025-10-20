<?php

declare(strict_types=1);

namespace FBE;

/**
 * Base interface for FBE struct models
 */
interface Model
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
     * Verify model
     */
    public function verify(): bool;

    /**
     * Get model size
     */
    public function fbeSize(): int;

    /**
     * Serialize value and return serialized size
     */
    public function serialize(mixed $value): int;

    /**
     * Deserialize value and return [value, deserialized size]
     */
    public function deserialize(mixed $value): array;

    /**
     * Move to next position (for streaming)
     */
    public function next(int $size): void;
}

