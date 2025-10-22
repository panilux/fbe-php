<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding vector final model (inline, compact)
 * Generic container for any item type
 */
final class FinalModelVector extends FinalModel
{
    private FinalModel $itemModel;

    public function __construct($buffer, int $offset, FinalModel $itemModel)
    {
        parent::__construct($buffer, $offset);
        $this->itemModel = $itemModel;
    }

    public function size(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return 4; // Count only
        }

        $count = $this->buffer->readUInt32($this->offset);
        $totalSize = 4; // Count
        $itemOffset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $itemSize = $this->itemModel->size();
            $totalSize += $itemSize;
            $itemOffset += $itemSize;
        }

        return $totalSize;
    }

    public function extra(): int
    {
        return 0; // Inline format
    }

    /**
     * Get vector values
     * @return array
     */
    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $count = $this->buffer->readUInt32($this->offset);
        $values = [];
        $itemOffset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $values[] = $this->itemModel->get();
            $itemSize = $this->itemModel->size();
            $itemOffset += $itemSize;
        }

        return $values;
    }

    /**
     * Set vector values
     * @param array $values
     */
    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeUInt32($this->offset, count($values));
        $itemOffset = $this->offset + 4;

        foreach ($values as $value) {
            $this->itemModel->setOffset($itemOffset);
            $this->itemModel->set($value);
            $itemSize = $this->itemModel->size();
            $itemOffset += $itemSize;
        }
    }
}

