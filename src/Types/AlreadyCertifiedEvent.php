<?php

namespace Suicore\Walrus\Types;

class AlreadyCertifiedEvent
{
    private string $txDigest;
    private string $eventSeq;

    public function __construct(string $txDigest, string $eventSeq)
    {
        $this->txDigest = $txDigest;
        $this->eventSeq = $eventSeq;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['txDigest'], $data['eventSeq'])) {
            throw new \InvalidArgumentException("Invalid data for AlreadyCertifiedEvent");
        }
        return new self($data['txDigest'], $data['eventSeq']);
    }

    public function getTxDigest(): string
    {
        return $this->txDigest;
    }
    public function getEventSeq(): string
    {
        return $this->eventSeq;
    }
}
