<?php

declare(strict_types=1);

namespace FBE\Test;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelInt32;
use FBE\FieldModelString;

/**
 * User struct model (PHP 8.4+)
 * 
 * Example struct with FieldModel pattern:
 * - id: int32
 * - name: string
 * - side: int8 (enum)
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class UserModel
{
    private FieldModelInt32 $id;
    private FieldModelString $name;
    
    public function __construct(WriteBuffer|ReadBuffer $buffer, int $offset = 0)
    {
        // Initialize field models with correct offsets
        $this->id = new FieldModelInt32($buffer, $offset);
        $this->name = new FieldModelString($buffer, $offset + 4);
        // side at offset + 4 + string_size (dynamic)
    }

    /**
     * Get struct size (fixed part only)
     */
    public function size(): int
    {
        return 4 + 4 + 1; // id + string_size + side
    }

    /**
     * Serialize User to buffer
     */
    public function serialize(User $user, WriteBuffer $buffer): int
    {
        
        // Write id
        $this->id->set($user->id);
        
        // Write name
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
        
        // Read name
        $name = $this->name->get();
        
        // Read side (after string)
        $sideOffset = 4 + 4 + strlen($name);
        $side = Side::from($buffer->readInt8($sideOffset));
        
        return new User($id, $name, $side);
    }
}

