<?php

namespace Suicore\Walrus\Types;

class BlobObject
{
    private string $id;
    private int $registeredEpoch;
    private string $blobId;
    private int $size;
    private string $encodingType;
    private int | null $certifiedEpoch;
    private Storage $storage;
    private bool $deletable;

    public function __construct(
        string $id,
        int $registeredEpoch,
        string $blobId,
        int $size,
        string $encodingType,
        int | null $certifiedEpoch,
        Storage $storage,
        bool $deletable
    ) {
        $this->id = $id;
        $this->registeredEpoch = $registeredEpoch;
        $this->blobId = $blobId;
        $this->size = $size;
        $this->encodingType = $encodingType;
        $this->certifiedEpoch = $certifiedEpoch;
        $this->storage = $storage;
        $this->deletable = $deletable;
    }

    public static function fromArray(array $data): self
    {
        if (
            !isset(
                $data['id'],
                $data['registeredEpoch'],
                $data['blobId'],
                $data['size'],
                $data['encodingType'],
                $data['storage'],
                $data['deletable']
            )
        ) {
            throw new \InvalidArgumentException("Invalid data for BlobObject");
        }
        $storage = Storage::fromArray($data['storage']);

        return new self(
            $data['id'],
            (int)$data['registeredEpoch'],
            $data['blobId'],
            (int)$data['size'],
            $data['encodingType'],
            isset($data['certifiedEpoch']) ? (int)$data['certifiedEpoch'] : null,
            $storage,
            (bool)$data['deletable']
        );
    }

    public function getId(): string
    {
        return $this->id;
    }
    public function getRegisteredEpoch(): int
    {
        return $this->registeredEpoch;
    }
    public function getBlobId(): string
    {
        return $this->blobId;
    }
    public function getSize(): int
    {
        return $this->size;
    }
    public function getEncodingType(): string
    {
        return $this->encodingType;
    }
    public function getCertifiedEpoch(): int | null
    {
        return $this->certifiedEpoch;
    }
    public function getStorage(): Storage
    {
        return $this->storage;
    }
    public function isDeletable(): bool
    {
        return $this->deletable;
    }
}
