<?php

namespace Suicore\Walrus\Types;

class QuiltElement
{
    private string $identifier;
    private string $quiltPatchId;

    public function __construct(string $identifier, string $quiltPatchId)
    {
        $this->identifier = $identifier;
        $this->quiltPatchId = $quiltPatchId;
    }

    public static function fromObject(\stdClass $data): self
    {
        if (!isset($data->identifier, $data->quiltPatchId)) {
            throw new \InvalidArgumentException("Invalid data for QuiltElement");
        }
        return new self($data->identifier, $data->quiltPatchId);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getQuiltPatchId(): string
    {
        return $this->quiltPatchId;
    }
}
