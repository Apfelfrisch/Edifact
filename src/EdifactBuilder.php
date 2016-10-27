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
        $this->classes[$key]['builder'] = $builderClass;
    }

    public function build($key, $to, $filename = null)
    {
        if (isset($this->classes[$key])) {
            return new $this->classes[$key]['builder'](
                $this->configuration->getExportSender(),
                $to,
                $this->getFullpath($this->configuration->getFilePath(), $filename),
                $this->configuration
            );
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
