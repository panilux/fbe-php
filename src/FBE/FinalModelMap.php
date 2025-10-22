<?php

declare(strict_types=1);

namespace FBE;

final class FinalModelMap extends FinalModel
{
    private FinalModel $keyModel;
    private FinalModel $valueModel;

    public function __construct($buffer, int $offset, FinalModel $keyModel, FinalModel $valueModel)
    {
        parent::__construct($buffer, $offset);
        $this->keyModel = $keyModel;
        $this->valueModel = $valueModel;
    }

    public function size(): int
    {
        if (!($this->buffer instanceof ReadBuffer)) {
            return 4;
        }

        $count = $this->buffer->readUInt32($this->offset);
        $totalSize = 4;
        $itemOffset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            $this->keyModel->setOffset($itemOffset);
            $keySize = $this->keyModel->size();
            $totalSize += $keySize;
            $itemOffset += $keySize;

            $this->valueModel->setOffset($itemOffset);
            $valueSize = $this->valueModel->size();
            $totalSize += $valueSize;
            $itemOffset += $valueSize;
        }

        return $totalSize;
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

        $count = $this->buffer->readUInt32($this->offset);
        $map = [];
        $itemOffset = $this->offset + 4;

        for ($i = 0; $i < $count; $i++) {
            $this->keyModel->setOffset($itemOffset);
            $key = $this->keyModel->get();
            $itemOffset += $this->keyModel->size();

            $this->valueModel->setOffset($itemOffset);
            $value = $this->valueModel->get();
            $itemOffset += $this->valueModel->size();

            $map[$key] = $value;
        }

        return $map;
    }

    public function set(array $map): void
    {
        if (!($this->buffer instanceof WriteBuffer)) {
            throw new \RuntimeException("Cannot write to ReadBuffer");
        }

        $this->buffer->writeUInt32($this->offset, count($map));
        $itemOffset = $this->offset + 4;

        foreach ($map as $key => $value) {
            $this->keyModel->setOffset($itemOffset);
            $this->keyModel->set($key);
            $itemOffset += $this->keyModel->size();

            $this->valueModel->setOffset($itemOffset);
            $this->valueModel->set($value);
            $itemOffset += $this->valueModel->size();
        }
    }
}
