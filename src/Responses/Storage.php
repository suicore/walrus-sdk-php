<?php

namespace Suicore\Walrus\Responses;

class Storage
{
    private string $id;
    private int $startEpoch;
    private int $endEpoch;
    private int $storageSize;

    public function __construct(string $id, int $startEpoch, int $endEpoch, int $storageSize)
    {
        $this->id = $id;
        $this->startEpoch = $startEpoch;
        $this->endEpoch = $endEpoch;
        $this->storageSize = $storageSize;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['id'], $data['startEpoch'], $data['endEpoch'], $data['storageSize'])) {
            throw new \InvalidArgumentException("Invalid data for Storage");
        }
        return new self(
            $data['id'],
            (int)$data['startEpoch'],
            (int)$data['endEpoch'],
            (int)$data['storageSize']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
    public function getStartEpoch(): int
    {
        return $this->startEpoch;
    }
    public function getEndEpoch(): int
    {
        return $this->endEpoch;
    }
    public function getStorageSize(): int
    {
        return $this->storageSize;
    }
}
