<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Exceptions\EdifactException;

class Edifact
{
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function build($key, $to, $filename = null)
    {
        if ($builder = $this->configuration->getBuilder($key)) {
            return new $builder(
                $to,
                $filename,
                $this->configuration
            );
        }

        throw new EdifactException("Class with Key '$key' not registered.");
    }

    public function resolveFromFile($filepath)
    {
        return Message::fromFilepath($filepath, $this->configuration);
    }

    public function resolveFromString($string, $filename = 'php://temp')
    {
        return Message::fromString($string, $this->configuration, $filename);
    }
}
