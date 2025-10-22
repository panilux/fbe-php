<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding map field model (pointer-based)
 * Generic container for key-value pairs
 */
final class FieldModelMap extends FieldModel
{
    private FieldModel $keyModel;
    private FieldModel $valueModel;

    public function __construct($buffer, int $offset, FieldModel $keyModel, FieldModel $valueModel)
    {
        parent::__construct($buffer, $offset);
        $this->keyModel = $keyModel;
        $this->valueModel = $valueModel;
    }

    public function size(): int
    {
        return 4; // Pointer size
    }

    public function extra(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return 0;
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return 0;
        }

        // Read count at pointer location
        $count = $this->buffer->readUInt32($pointer);
        
        // Calculate total size: 4-byte count + key-value pairs
        $totalSize = 4;
        $itemOffset = $pointer + 4;
        
        for ($i = 0; $i < $count; $i++) {
            // Key
            $this->keyModel->setOffset($itemOffset);
            $keySize = $this->keyModel->size() + $this->keyModel->extra();
            $totalSize += $keySize;
            $itemOffset += $keySize;
            
            // Value
            $this->valueModel->setOffset($itemOffset);
            $valueSize = $this->valueModel->size() + $this->valueModel->extra();
            $totalSize += $valueSize;
            $itemOffset += $valueSize;
        }

        return $totalSize;
    }

    /**
     * Get map values
     * @return array Associative array
     */
    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $pointer = $this->buffer->readUInt32($this->offset);
        if ($pointer === 0) {
            return [];
        }

        $count = $this->buffer->readUInt32($pointer);
        $map = [];
        $itemOffset = $pointer + 4;

        for ($i = 0; $i < $count; $i++) {
            // Read key
            $this->keyModel->setOffset($itemOffset);
            $key = $this->keyModel->get();
            $keySize = $this->keyModel->size() + $this->keyModel->extra();
            $itemOffset += $keySize;
            
            // Read value
            $this->valueModel->setOffset($itemOffset);
            $value = $this->valueModel->get();
            $valueSize = $this->valueModel->size() + $this->valueModel->extra();
            $itemOffset += $valueSize;
            
            $map[$key] = $value;
        }

        return $map;
    }

    /**
     * Set map values
     * @param array $map Associative array
     */
    public function set(array $map): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        // Write count + key-value pairs at end of buffer
        $dataOffset = $this->buffer->size();
        $this->buffer->writeUInt32($dataOffset, count($map));
        $itemOffset = $dataOffset + 4;

        foreach ($map as $key => $value) {
            // Write key
            $this->keyModel->setOffset($itemOffset);
            $this->keyModel->set($key);
            $keySize = $this->keyModel->size() + $this->keyModel->extra();
            $itemOffset += $keySize;
            
            // Write value
            $this->valueModel->setOffset($itemOffset);
            $this->valueModel->set($value);
            $valueSize = $this->valueModel->size() + $this->valueModel->extra();
            $itemOffset += $valueSize;
        }

        // Write pointer
        $this->buffer->writeUInt32($this->offset, $dataOffset - $this->buffer->offset);
    }
}

