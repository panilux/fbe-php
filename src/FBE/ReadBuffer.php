<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding read buffer (PHP 8.4+)
 * 
 * Modern implementation using property hooks, readonly properties,
 * and other PHP 8.4 features while maintaining FBE binary compatibility.
 * 
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */
final class ReadBuffer
{
    /**
     * Buffer data (immutable after construction)
     */
    public private(set) string $buffer;
    
    /**
     * Current offset for reading
     */
    public private(set) int $offset {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException("Offset cannot be negative");
            }
            $this->offset = $value;
        }
    }
    
    /**
     * Buffer size
     */
    public private(set) int $size {
        set {
            if ($value < 0) {
                throw new \InvalidArgumentException("Size cannot be negative");
            }
            $this->size = $value;
        }
    }

    public function __construct(string $buffer = '', int $offset = 0, int $size = 0)
    {
        $this->buffer = $buffer;
        $this->offset = $offset;
        $this->size = $size === 0 ? strlen($buffer) : $size;
    }

    public function isEmpty(): bool
    {
        return $this->size === 0;
    }

    /**
     * Read bool value
     */
    public function readBool(int $offset): bool
    {
        return $this->buffer[$this->offset + $offset] !== "\x00";
    }

    /**
     * Read int8 value
     */
    public function readInt8(int $offset): int
    {
        return unpack('c', $this->buffer[$this->offset + $offset])[1];
    }

    /**
     * Read uint8 value
     */
    public function readUInt8(int $offset): int
    {
        return unpack('C', $this->buffer[$this->offset + $offset])[1];
    }

    /**
     * Read int16 value (little-endian)
     */
    public function readInt16(int $offset): int
    {
        return unpack('s', substr($this->buffer, $this->offset + $offset, 2))[1];
    }

    /**
     * Read uint16 value (little-endian)
     */
    public function readUInt16(int $offset): int
    {
        return unpack('v', substr($this->buffer, $this->offset + $offset, 2))[1];
    }

    /**
     * Read int32 value (little-endian)
     */
    public function readInt32(int $offset): int
    {
        return unpack('l', substr($this->buffer, $this->offset + $offset, 4))[1];
    }

    /**
     * Read uint32 value (little-endian)
     */
    public function readUInt32(int $offset): int
    {
        return unpack('V', substr($this->buffer, $this->offset + $offset, 4))[1];
    }

    /**
     * Read int64 value (little-endian)
     */
    public function readInt64(int $offset): int
    {
        return unpack('q', substr($this->buffer, $this->offset + $offset, 8))[1];
    }

    /**
     * Read uint64 value (little-endian)
     */
    public function readUInt64(int $offset): int
    {
        return unpack('P', substr($this->buffer, $this->offset + $offset, 8))[1];
    }

    /**
     * Read float value (little-endian)
     */
    public function readFloat(int $offset): float
    {
        return unpack('f', substr($this->buffer, $this->offset + $offset, 4))[1];
    }

    /**
     * Read double value (little-endian)
     */
    public function readDouble(int $offset): float
    {
        return unpack('d', substr($this->buffer, $this->offset + $offset, 8))[1];
    }

    /**
     * Read string value (size-prefixed)
     * Format: 4-byte size + UTF-8 bytes
     */
    public function readString(int $offset): string
    {
        $size = $this->readUInt32($offset);
        
        if ($size === 0) {
            return '';
        }
        
        return substr($this->buffer, $this->offset + $offset + 4, $size);
    }

    /**
     * Read timestamp value (uint64, nanoseconds since epoch)
     */
    public function readTimestamp(int $offset): int
    {
        return $this->readUInt64($offset);
    }

    /**
     * Read UUID value (16 bytes)
     */
    public function readUuid(int $offset): string
    {
        $binary = substr($this->buffer, $this->offset + $offset, 16);
        $hex = bin2hex($binary);
        
        // Format as UUID string: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    /**
     * Read bytes value (size-prefixed binary data)
     */
    public function readBytes(int $offset): string
    {
        $size = $this->readUInt32($offset);
        
        if ($size === 0) {
            return '';
        }
        
        return substr($this->buffer, $this->offset + $offset + 4, $size);
    }

    /**
     * Read decimal value (.NET Decimal format, 16 bytes)
     * Returns array with 'value', 'scale', 'negative' keys
     */
    public function readDecimal(int $offset): array
    {
        // Bytes 0-11: Unscaled value (96-bit, little-endian)
        $low = $this->readUInt32($offset);
        $mid = $this->readUInt32($offset + 4);
        $high = $this->readUInt32($offset + 8);
        
        // Combine into single value (simplified for 64-bit range)
        $value = $low | ($mid << 32);
        
        // Byte 14: Scale
        $scale = unpack('C', $this->buffer[$this->offset + $offset + 14])[1];
        
        // Byte 15: Sign
        $negative = $this->buffer[$this->offset + $offset + 15] === "\x80";
        
        return [
            'value' => $value,
            'scale' => $scale,
            'negative' => $negative,
        ];
    }

    /**
     * Read vector of int32 values
     * Format: 4-byte offset pointer → (4-byte size + elements)
     */
    public function readVectorInt32(int $offset): array
    {
        // Read pointer
        $dataOffset = $this->readUInt32($offset);
        if ($dataOffset === 0) {
            return [];
        }
        
        // Read size
        $size = $this->readUInt32($dataOffset);
        
        // Read elements
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readInt32($dataOffset + 4 + ($i * 4));
        }
        
        return $result;
    }

    /**
     * Read fixed-size array of int32 values (inline, no pointer)
     * Format: N × 4 bytes (elements only)
     */
    public function readArrayInt32(int $offset, int $size): array
    {
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $result[] = $this->readInt32($offset + ($i * 4));
        }
        return $result;
    }

    /**
     * Read map of int32 key-value pairs
     * Format: 4-byte offset pointer → (4-byte size + key-value pairs)
     * @return array Associative array of key => value pairs
     */
    public function readMapInt32(int $offset): array
    {
        // Read pointer
        $dataOffset = $this->readUInt32($offset);
        if ($dataOffset === 0) {
            return [];
        }
        
        // Read size
        $size = $this->readUInt32($dataOffset);
        
        // Read key-value pairs
        $result = [];
        for ($i = 0; $i < $size; $i++) {
            $key = $this->readInt32($dataOffset + 4 + ($i * 8));
            $value = $this->readInt32($dataOffset + 4 + ($i * 8) + 4);
            $result[$key] = $value;
        }
        
        return $result;
    }

    /**
     * Read set of int32 values (same format as vector)
     * Format: 4-byte offset pointer → (4-byte size + elements)
     */
    public function readSetInt32(int $offset): array
    {
        return $this->readVectorInt32($offset);
    }

    // ========================================================================
    // Collections (String)
    // ========================================================================

    /**
     * Read vector of string values
     */
    public function readVectorString(int $offset): array
    {
        $pointer = $this->readUInt32($offset);
        if ($pointer == 0) return [];
        
        $size = $this->readUInt32($pointer);
        $values = [];
        $currentOffset = $pointer + 4;
        
        for ($i = 0; $i < $size; $i++) {
            $str = $this->readString($currentOffset);
            $values[] = $str;
            $currentOffset += 4 + strlen($str);
        }
        
        return $values;
    }

    /**
     * Read fixed-size array of strings
     */
    public function readArrayString(int $offset, int $count): array
    {
        $values = [];
        $currentOffset = $offset;
        
        for ($i = 0; $i < $count; $i++) {
            $str = $this->readString($currentOffset);
            $values[] = $str;
            $currentOffset += 4 + strlen($str);
        }
        
        return $values;
    }

    // ========================================================================
    // Collections (Float/Double)
    // ========================================================================

    public function readVectorFloat(int $offset): array
    {
        $pointer = $this->readUInt32($offset);
        if ($pointer == 0) return [];
        $size = $this->readUInt32($pointer);
        $values = [];
        for ($i = 0; $i < $size; $i++) {
            $values[] = $this->readFloat($pointer + 4 + ($i * 4));
        }
        return $values;
    }

    public function readArrayFloat(int $offset, int $count): array
    {
        $values = [];
        for ($i = 0; $i < $count; $i++) {
            $values[] = $this->readFloat($offset + ($i * 4));
        }
        return $values;
    }

    public function readVectorDouble(int $offset): array
    {
        $pointer = $this->readUInt32($offset);
        if ($pointer == 0) return [];
        $size = $this->readUInt32($pointer);
        $values = [];
        for ($i = 0; $i < $size; $i++) {
            $values[] = $this->readDouble($pointer + 4 + ($i * 8));
        }
        return $values;
    }

    public function readArrayDouble(int $offset, int $count): array
    {
        $values = [];
        for ($i = 0; $i < $count; $i++) {
            $values[] = $this->readDouble($offset + ($i * 8));
        }
        return $values;
    }

    // Optional types
    public function hasValue(int $offset): bool
    {
        return $this->readUInt8($offset) !== 0;
    }

    public function readOptionalInt32(int $offset): ?int
    {
        if (!$this->hasValue($offset)) {
            return null;
        }
        
        $dataOffset = $this->readUInt32($offset + 1);
        return $this->readInt32($dataOffset);
    }

    public function readOptionalString(int $offset): ?string
    {
        if (!$this->hasValue($offset)) {
            return null;
        }
        
        $dataOffset = $this->readUInt32($offset + 1);
        return $this->readString($dataOffset);
    }

    public function readOptionalDouble(int $offset): ?float
    {
        if (!$this->hasValue($offset)) {
            return null;
        }
        
        $dataOffset = $this->readUInt32($offset + 1);
        return $this->readDouble($dataOffset);
    }
}
