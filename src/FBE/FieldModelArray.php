<?php

declare(strict_types=1);

namespace FBE;

/**
 * Fast Binary Encoding array field model (inline, fixed size)
 * Generic container for any item type with fixed size
 */
final class FieldModelArray extends FieldModel
{
    private FieldModel $itemModel;
    private int $arraySize;

    public function __construct($buffer, int $offset, FieldModel $itemModel, int $arraySize)
    {
        parent::__construct($buffer, $offset);
        $this->itemModel = $itemModel;
        $this->arraySize = $arraySize;
    }

    public function size(): int
    {
        // Array is inline: size = item_size * count
        return ($this->itemModel->size() + $this->itemModel->extra()) * $this->arraySize;
    }

    public function extra(): int
    {
        return 0; // Array is inline, no extra space
    }

    /**
     * Get array values
     * @return array
     */
    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $values = [];
        $itemOffset = $this->offset;
        $itemSize = $this->itemModel->size() + $this->itemModel->extra();

        for ($i = 0; $i < $this->arraySize; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $values[] = $this->itemModel->get();
            $itemOffset += $itemSize;
        }

        return $values;
    }

    /**
     * Set array values
     * @param array $values
     */
    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        if (count($values) !== $this->arraySize) {
            throw new \InvalidArgumentException("Array size mismatch: expected {$this->arraySize}, got " . count($values));
        }

        $itemOffset = $this->offset;
        $itemSize = $this->itemModel->size() + $this->itemModel->extra();

        foreach ($values as $value) {
            $this->itemModel->setOffset($itemOffset);
            $this->itemModel->set($value);
            $itemOffset += $itemSize;
        }
    }
}

