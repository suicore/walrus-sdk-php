<?php

namespace Suicore\Walrus\Types;

class StoreBlobOrQuiltOptions
{
    private int $epochs;
    private string $sendObjectTo;
    private bool $deletable;

    public function __construct(int $epochs = 1, string $sendObjectTo = '', bool $deletable = false)
    {
        if ($epochs < 1) {
            throw new \InvalidArgumentException('Epochs must equal to or greater than 1');
        }
        $this->epochs = $epochs;
        $this->sendObjectTo = $sendObjectTo;
        $this->deletable = $deletable;
    }

    public function getEpochs(): int
    {
        return $this->epochs;
    }

    public function getSendObjectTo(): string
    {
        return $this->sendObjectTo;
    }

    public function isDeletable(): bool
    {
        return $this->deletable;
    }
}
