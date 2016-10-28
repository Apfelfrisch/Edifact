<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactBuilder
{
    private $classes = [];

    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;
    }

    public function addBuilder($key, $builderClass)
    {
        $this->classes[$key] = $builderClass;
    }

    public function build($key, $to, $filename = null)
    {
        if (isset($this->classes[$key])) {
            return new $this->classes[$key](
                $to,
                $filename,
                $this->configuration
            );
        }

        throw new EdifactException("Class with Key '$key' not registered.");
    }

}
