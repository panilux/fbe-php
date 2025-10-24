<?php

declare(strict_types=1);

namespace FBE\Test;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FinalModelInt32;
use FBE\FinalModelString;

/**
 * User struct final model (PHP 8.4+)
 *
 * Example struct with FinalModel pattern (inline format):
 * - id: int32
 * - name: string
 * - side: int8 (enum)
 */
final class UserFinalModel
{
    private FinalModelInt32 $id;
    private FinalModelString $name;

    public function __construct(WriteBuffer|ReadBuffer $buffer, int $offset = 0)
    {
        // Initialize final models with correct offsets
        $this->id = new FinalModelInt32($buffer, $offset);
        $this->name = new FinalModelString($buffer, $offset + 4);
        // side at offset + 4 + 4 + string_length (inline)
    }

    /**
     * Get struct size (dynamic based on string length)
     */
    public function size(): int
    {
        return 4 + $this->name->size() + 1; // id + string + side
    }

    /**
     * Serialize User to buffer
     */
    public function serialize(User $user, WriteBuffer $buffer): int
    {
        // Write id
        $this->id->set($user->id);

        // Write name (inline)
        $this->name->set($user->name);

        // Write side (after string)
        $sideOffset = 4 + 4 + strlen($user->name);
        $buffer->writeInt8($sideOffset, $user->side->value);

        return $sideOffset + 1; // Total size
    }

    /**
     * Deserialize User from buffer
     */
    public function deserialize(ReadBuffer $buffer): User
    {
        // Read id
        $id = $this->id->get();

        // Read name (inline)
        $name = $this->name->get();

        // Read side (after string)
        $sideOffset = 4 + 4 + strlen($name);
        $side = Side::from($buffer->readInt8($sideOffset));

        return new User($id, $name, $side);
    }
}

