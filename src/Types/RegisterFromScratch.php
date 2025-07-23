<?php

namespace Suicore\Walrus\Types;

class RegisterFromScratch
{
    private int $encodedLength;
    private int $epochsAhead;

    public function __construct(int $encodedLength, int $epochsAhead)
    {
        $this->encodedLength = $encodedLength;
        $this->epochsAhead = $epochsAhead;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['encodedLength'], $data['epochsAhead'])) {
            throw new \InvalidArgumentException("Invalid data for RegisterFromScratch");
        }
        return new self(
            (int)$data['encodedLength'],
            (int)$data['epochsAhead']
        );
    }

    public function getEncodedLength(): int
    {
        return $this->encodedLength;
    }
    public function getEpochsAhead(): int
    {
        return $this->epochsAhead;
    }
}
