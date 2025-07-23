<?php

namespace Suicore\Walrus\Types;

class StoredQuiltBlobs
{
    private array $files;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public static function fromArray(array $files): self
    {
        $files = array_map(function ($file) {
            $obj = new \stdClass();
            $obj->identifier = $file["identifier"];
            $obj->quiltPatchId = $file["quiltPatchId"];
            return QuiltElement::fromObject($obj);
        }, $files);
        return new self($files);
    }

    public function getQuiltFiles(): array
    {
        return $this->files;
    }
}
