<?php

namespace Suicore\Walrus\Responses;

class AlreadyCertifiedResponse
{
    private string $blobId;
    private AlreadyCertifiedEvent $event;
    private int $endEpoch;

    public function __construct(string $blobId, AlreadyCertifiedEvent $event, int $endEpoch)
    {
        $this->blobId = $blobId;
        $this->event = $event;
        $this->endEpoch = $endEpoch;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['blobId'], $data['event'], $data['endEpoch'])) {
            throw new \InvalidArgumentException("Invalid data for AlreadyCertifiedResponse");
        }
        $event = AlreadyCertifiedEvent::fromArray($data['event']);
        return new self($data['blobId'], $event, (int)$data['endEpoch']);
    }

    public function getBlobId(): string
    {
        return $this->blobId;
    }
    public function getEvent(): AlreadyCertifiedEvent
    {
        return $this->event;
    }
    public function getEndEpoch(): int
    {
        return $this->endEpoch;
    }
}
