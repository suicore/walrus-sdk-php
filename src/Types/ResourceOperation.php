<?php

namespace Suicore\Walrus\Types;

class ResourceOperation
{
    private RegisterFromScratch $registerFromScratch;

    public function __construct(RegisterFromScratch $registerFromScratch)
    {
        $this->registerFromScratch = $registerFromScratch;
    }

    public static function fromArray(array $data): self
    {
        if (!isset($data['registerFromScratch'])) {
            throw new \InvalidArgumentException("Invalid data for ResourceOperation");
        }
        $rfs = RegisterFromScratch::fromArray($data['registerFromScratch']);
        return new self($rfs);
    }

    public function getRegisterFromScratch(): RegisterFromScratch
    {
        return $this->registerFromScratch;
    }
}
