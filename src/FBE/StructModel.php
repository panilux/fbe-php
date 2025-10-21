<?php
/**
 * FBE StructModel - Serialization with 4-byte size header
 * Supports protocol versioning and forward/backward compatibility
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

namespace FBE;

use FBE\WriteBuffer;
use FBE\ReadBuffer;

abstract class StructModel
{
    protected WriteBuffer $buffer;
    
    public function __construct(?WriteBuffer $buffer = null)
    {
        $this->buffer = $buffer ?? new WriteBuffer();
    }
    
    public function getBuffer(): WriteBuffer
    {
        return $this->buffer;
    }
    
    /**
     * Get the struct data size (without header)
     */
    abstract protected function getStructSize($value): int;
    
    /**
     * Serialize struct data (without header)
     */
    abstract protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int;
    
    /**
     * Deserialize struct data (without header)
     */
    abstract protected function deserializeStruct(ReadBuffer $buffer, int $offset);
    
    /**
     * Serialize struct with 4-byte size header
     * Format: [4-byte size][struct data]
     * Returns total size written (header + data)
     */
    public function serialize($value): int
    {
        // Calculate struct size
        $structSize = $this->getStructSize($value);
        $totalSize = 4 + $structSize;  // Header + data
        
        // Write size header
        $this->buffer->writeUInt32(0, $totalSize);
        
        // Write struct data
        $this->serializeStruct($value, $this->buffer, 4);
        
        return $totalSize;
    }
    
    /**
     * Deserialize struct from buffer with 4-byte size header
     * Returns [value, total_size_read]
     */
    public function deserialize()
    {
        $readBuffer = new ReadBuffer($this->buffer->data());
        
        // Read size header
        $totalSize = $readBuffer->readUInt32(0);
        
        // Deserialize struct data
        $value = $this->deserializeStruct($readBuffer, 4);
        
        return [$value, $totalSize];
    }
}

