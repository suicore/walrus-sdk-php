<?php

namespace Suicore\Walrus\Responses;

class NewlyCreatedResponse
{
    private BlobObject $blobObject;
    private ResourceOperation $resourceOperation;
    private int $cost;

    public function __construct(BlobObject $blobObject, ResourceOperation $resourceOperation, int $cost)
    {
        $this->blobObject = $blobObject;
        $this->resourceOperation = $resourceOperation;
        $this->cost = $cost;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['blobObject'], $data['resourceOperation'], $data['cost'])) {
            throw new \InvalidArgumentException("Invalid data for NewlyCreatedResponse");
        }
        $blobObject = BlobObject::fromArray($data['blobObject']);
        $resourceOperation = ResourceOperation::fromArray($data['resourceOperation']);
        $cost = (int)$data['cost'];

        return new self($blobObject, $resourceOperation, $cost);
    }

    public function getBlobObject(): BlobObject
    {
        return $this->blobObject;
    }

    public function getResourceOperation(): ResourceOperation
    {
        return $this->resourceOperation;
    }

    public function getCost(): int
    {
        return $this->cost;
    }
}
