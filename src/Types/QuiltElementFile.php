<?php

namespace Suicore\Walrus\Types;

/**
 * @property string $identifier
 * @property string|resource $source
 */
class QuiltElementFile
{
    private string $identifier;

    /** @var string|resource */
    private $source;

    /**
     * @param string $identifier
     * @param string|resource $source
     */
    public function __construct(string $identifier, $source)
    {
        $this->identifier = $identifier;
        $this->source = $source;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /** @return string|resource */
    public function getSource()
    {
        return $this->source;
    }
}
