<?php

namespace Suicore\Walrus\Responses;

class StoreBlobResponse
{
    /**
     * @param NewlyCreatedResponse|null $newlyCreated
     * @param AlreadyCertifiedResponse|null $alreadyCertified
     */
    public function __construct(
        private ?NewlyCreatedResponse $newlyCreated = null,
        private ?AlreadyCertifiedResponse $alreadyCertified = null
    ) {
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON: " . json_last_error_msg());
        }

        $newlyCreated = isset($data['newlyCreated'])
            ? NewlyCreatedResponse::fromArray($data['newlyCreated'])
            : null;
        $alreadyCertified = isset($data['alreadyCertified'])
            ? AlreadyCertifiedResponse::fromArray($data['alreadyCertified'])
            : null;

        return new self($newlyCreated, $alreadyCertified);
    }

    public function isNewlyCreated(): bool
    {
        return $this->newlyCreated !== null;
    }

    public function isAlreadyCertified(): bool
    {
        return $this->alreadyCertified !== null;
    }

    public function getNewlyCreated(): ?NewlyCreatedResponse
    {
        return $this->newlyCreated;
    }

    public function getAlreadyCertified(): ?AlreadyCertifiedResponse
    {
        return $this->alreadyCertified;
    }
}
