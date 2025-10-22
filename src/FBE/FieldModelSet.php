<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding set field model (pointer-based)
 * Generic container for unique values
 */
final class FieldModelSet extends FieldModel
{
    private FieldModel $itemModel;

    public function __construct($buffer, int $offset, FieldModel $itemModel)
    {
        parent::__construct($buffer, $offset);
        $this->itemModel = $itemModel;
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
        
        // Calculate total size: 4-byte count + items
        $totalSize = 4;
        $itemOffset = $pointer + 4;
        
        for ($i = 0; $i < $count; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $itemSize = $this->itemModel->size() + $this->itemModel->extra();
            $totalSize += $itemSize;
            $itemOffset += $itemSize;
        }

        return $totalSize;
    }

    /**
     * Get set values
     * @return array Array of unique values
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
        $values = [];
        $itemOffset = $pointer + 4;

        for ($i = 0; $i < $count; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $values[] = $this->itemModel->get();
            $itemSize = $this->itemModel->size() + $this->itemModel->extra();
            $itemOffset += $itemSize;
        }

        return array_unique($values); // Ensure uniqueness
    }

    /**
     * Set set values
     * @param array $values
     */
    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        // Ensure uniqueness
        $values = array_unique($values);

        // Write count + items at end of buffer
        $dataOffset = $this->buffer->size();
        $this->buffer->writeUInt32($dataOffset, count($values));
        $itemOffset = $dataOffset + 4;

        foreach ($values as $value) {
            $this->itemModel->setOffset($itemOffset);
            $this->itemModel->set($value);
            $itemSize = $this->itemModel->size() + $this->itemModel->extra();
            $itemOffset += $itemSize;
        }

        // Write pointer
        $this->buffer->writeUInt32($this->offset, $dataOffset - $this->buffer->offset);
    }
}

