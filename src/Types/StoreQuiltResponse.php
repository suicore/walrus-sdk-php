<?php

namespace Suicore\Walrus\Types;

class StoreQuiltResponse
{
    /**
     * @param StoreBlobResponse|null $blobStoreResult
     * @param StoredQuiltBlobs|null $storedQuiltBlobs
     */
    public function __construct(
        private readonly ?StoreBlobResponse $blobStoreResult = null,
        private readonly ?StoredQuiltBlobs $storedQuiltBlobs = null
    ) {
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }

        $blobStoreResult = isset($data['blobStoreResult'])
            ? StoreBlobResponse::fromArray($data['blobStoreResult'])
            : null;

        $storedQuiltBlobs = isset($data['storedQuiltBlobs'])
            ? StoredQuiltBlobs::fromArray($data['storedQuiltBlobs'])
            : null;

        return new self($blobStoreResult, $storedQuiltBlobs);
    }

    public function isNewlyCreated(): bool
    {
        return $this->blobStoreResult->getNewlyCreated() !== null;
    }

    public function isAlreadyCertified(): bool
    {
        return $this->blobStoreResult->getAlreadyCertified() !== null;
    }

    public function getNewlyCreated(): ?NewlyCreatedResponse
    {
        return $this->blobStoreResult->getNewlyCreated();
    }

    public function getAlreadyCertified(): ?AlreadyCertifiedResponse
    {
        return $this->blobStoreResult->getAlreadyCertified();
    }

    public function getBlobStoreResult(): ?StoreBlobResponse
    {
        return $this->blobStoreResult;
    }

    public function getStoredQuiltBlobs(): ?StoredQuiltBlobs
    {
        return $this->storedQuiltBlobs;
    }
}
