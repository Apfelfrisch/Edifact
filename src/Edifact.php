<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\Interfaces\BuilderInterface;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Exceptions\EdifactException;

class Edifact
{
    /** @var Configuration */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $key
     * @param string $to
     * @param string $filename
     *
     * @return BuilderInterface
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public function build($key, $to, $filename = 'php://temp')
    {
        $builder = $this->configuration->getBuilder($key);

        if (null === $builder) {
            throw new EdifactException("Class with Key '$key' not registered.");
        }

        return new $builder($to, $this->configuration, $filename);
    }

    /**
     * @param string $string
     * @param string $filename
     *
     * @return Message
     */
    public function buildFromString($string, $filename = 'php://temp')
    {
        return Message::fromString($string, $this->configuration, $filename);
    }

    /**
     * @param string $filepath
     *
     * @return Message
     */
    public function resolveFromFile($filepath)
    {
        return Message::fromFilepath($filepath, $this->configuration);
    }

    /**
     * @param string $string
     *
     * @return Message
     */
    public function resolveFromString($string)
    {
        return $this->buildFromString($string);
    }
}
