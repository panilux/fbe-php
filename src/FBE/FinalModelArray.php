<?php

declare(strict_types=1);

namespace FBE;

final class FinalModelArray extends FinalModel
{
    private FinalModel $itemModel;
    private int $arraySize;

    public function __construct($buffer, int $offset, FinalModel $itemModel, int $arraySize)
    {
        parent::__construct($buffer, $offset);
        $this->itemModel = $itemModel;
        $this->arraySize = $arraySize;
    }

    public function size(): int
    {
        return $this->itemModel->size() * $this->arraySize;
    }

    public function extra(): int
    {
        return 0;
    }

    public function get(): array
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            throw new \RuntimeException("Cannot read from WriteBuffer");
        }

        $values = [];
        $itemOffset = $this->offset;
        $itemSize = $this->itemModel->size();

        for ($i = 0; $i < $this->arraySize; $i++) {
            $this->itemModel->setOffset($itemOffset);
            $values[] = $this->itemModel->get();
            $itemOffset += $itemSize;
        }

        return $values;
    }

    public function set(array $values): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        if (count($values) !== $this->arraySize) {
            throw new \InvalidArgumentException("Array size mismatch");
        }

        $itemOffset = $this->offset;
        $itemSize = $this->itemModel->size();

        foreach ($values as $value) {
            $this->itemModel->setOffset($itemOffset);
            $this->itemModel->set($value);
            $itemOffset += $itemSize;
        }
    }
}
