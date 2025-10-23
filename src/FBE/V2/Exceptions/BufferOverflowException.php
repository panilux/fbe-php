<?php

declare(strict_types=1);

namespace FBE\V2\Exceptions;

/**
 * Exception thrown when buffer bounds are violated
 *
 * SECURITY CRITICAL: This prevents buffer overflow attacks
 */
class BufferOverflowException extends BufferException
{
    public function __construct(
        public readonly int $attemptedOffset,
        public readonly int $attemptedLength,
        public readonly int $bufferSize,
        string $message = '',
    ) {
        if (empty($message)) {
            $message = sprintf(
                'Buffer overflow: attempted to access %d bytes at offset %d, but buffer size is %d',
                $attemptedLength,
                $attemptedOffset,
                $bufferSize
            );
        }

        parent::__construct($message);
    }
}
