<?php

namespace FBE\Test;

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelString;

/**
 * Address struct model (FBE pattern)
 */
class AddressModel
{
    private FieldModelString $cityField;
    private FieldModelString $countryField;
    
    public function __construct(
        private WriteBuffer|ReadBuffer $buffer,
        private int $offset = 0
    ) {
        // FBE struct header: 4 bytes (struct offset) + 4 bytes (struct size)
        $baseOffset = $offset + 8;
        
        // City field at base offset
        $this->cityField = new FieldModelString($buffer, $baseOffset);
        
        // Country field after city (string field size = 4 bytes pointer)
        $this->countryField = new FieldModelString($buffer, $baseOffset + 4);
    }
    
    /**
     * Get struct body size (2 string pointers = 8 bytes)
     */
    public function fbeSize(): int
    {
        return 8;
    }
    
    /**
     * Get total size including header
     */
    public function fbeOffset(): int
    {
        return $this->offset;
    }
    
    /**
     * Serialize Address to buffer
     */
    public function set(Address $value): void
    {
        // Write struct header
        $this->buffer->writeInt32($this->offset, 0); // struct offset (unused for now)
        $this->buffer->writeInt32($this->offset + 4, $this->fbeSize()); // struct size
        
        // Write fields
        $this->cityField->set($value->city);
        $this->countryField->set($value->country);
    }
    
    /**
     * Deserialize Address from buffer
     */
    public function get(): Address
    {
        $city = $this->cityField->get();
        $country = $this->countryField->get();
        
        return new Address($city, $country);
    }
}

