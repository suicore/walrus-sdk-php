<?php

namespace Suicore\Walrus\Types;

class QuiltElementFileMetadata
{
    private string $identifier;
    private array $tags;

    public function __construct(string $identifier, array $tags)
    {
        $this->identifier = $identifier;
        $this->tags = $tags;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function toArray(): array
    {
        $result = [
            'identifier' => $this->identifier
        ];
        if (!empty($this->tags)) {
            $result['tags'] = $this->tags;
        }

        return $result;
    }
}
