<?php

declare(strict_types=1);

namespace FBE\V2\Exceptions;

/**
 * Exception thrown when attempting to allocate negative space or use negative offsets
 */
class BufferUnderflowException extends BufferException
{
    public function __construct(
        public readonly int $invalidValue,
        string $context = 'offset',
    ) {
        parent::__construct(
            sprintf('Buffer underflow: %s cannot be negative (got %d)', $context, $invalidValue)
        );
    }
}
