<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Configuration;

class EdifactBuilder
{
    private $classes = [];

    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?: new Configuration;
    }

    public function addBuilder($key, $builderClass, $from, $filepath = null)
    {
        $this->classes[$key]['builder'] = $builderClass;
        $this->classes[$key]['construct'] = [$from, $filepath];
    }

    public function build($key, $to, $filename = null)
    {
        return $this->instanceClass($key, $to, $filename);
    }

    private function instanceClass($key, $to, $filename)
    {
        if (isset($this->classes[$key])) {
            list($from, $filepath) = $this->classes[$key]['construct'];
            return new $this->classes[$key]['builder']($from, $to, $this->getFullpath($filepath, $filename), $this->configuration);
        }

        throw new EdifactException("Class with Key '$key' not registered.");
    }

    private function getFullpath($filepath, $filename)
    {
        if ($filename === null) {
            return null;
        }
        if ($filepath === null && $filename != null) {
            return $filename;
        }
        return $filepath . '/' . $filename;
    }
}
