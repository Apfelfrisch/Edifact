<?php

namespace Proengeno\Edifact;

use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Interfaces\BuilderInterface;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Exceptions\EdifactException;

class Edifact
{
    public function __construct(protected ?Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration;
    }

    public function build(string $key, string $to, string $filename = 'php://temp'): BuilderInterface
    {
        $builder = $this->configuration->getBuilder($key);

        if (null === $builder) {
            throw new EdifactException("Class with Key '$key' not registered.");
        }

        return new $builder($to, $this->configuration, $filename);
    }

    public function buildFromString(string $string, string $filename = 'php://temp'): Message
    {
        return Message::fromString($string, $this->configuration, $filename);
    }

    public function resolveFromFile(string $filepath): Message
    {
        return Message::fromFilepath($filepath, $this->configuration);
    }

    public function resolveFromString(string $string): Message
    {
        return $this->buildFromString($string);
    }
}
