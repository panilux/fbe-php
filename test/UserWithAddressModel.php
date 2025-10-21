<?php

namespace FBE\Test;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelInt32;
use FBE\FieldModelString;

/**
 * UserWithAddress struct model (with nested Address)
 */
class UserWithAddressModel
{
    private FieldModelInt32 $idField;
    private FieldModelString $nameField;
    private AddressModel $addressField;
    
    public function __construct(
        private WriteBuffer|ReadBuffer $buffer,
        private int $offset = 0
    ) {
        // FBE struct header: 8 bytes
        $baseOffset = $offset + 8;
        
        // id field (4 bytes)
        $this->idField = new FieldModelInt32($buffer, $baseOffset);
        
        // name field (4 bytes pointer)
        $this->nameField = new FieldModelString($buffer, $baseOffset + 4);
        
        // address field (nested struct, 8 bytes header + 8 bytes body = 16 bytes total)
        $this->addressField = new AddressModel($buffer, $baseOffset + 8);
    }
    
    /**
     * Get struct body size
     */
    public function fbeSize(): int
    {
        return 4 + 4 + 16; // id + name + address
    }
    
    /**
     * Serialize UserWithAddress to buffer
     */
    public function set(UserWithAddress $value): void
    {
        // Write struct header
        $this->buffer->writeInt32($this->offset, 0);
        $this->buffer->writeInt32($this->offset + 4, $this->fbeSize());
        
        // Write fields
        $this->idField->set($value->id);
        $this->nameField->set($value->name);
        $this->addressField->set($value->address);
    }
    
    /**
     * Deserialize UserWithAddress from buffer
     */
    public function get(): UserWithAddress
    {
        $id = $this->idField->get();
        $name = $this->nameField->get();
        $address = $this->addressField->get();
        
        return new UserWithAddress($id, $name, $address);
    }
}

