<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactBuilder
{
    private $configurations = [];
    private $classes = [];

    public function addBuilder($key, $builderClass, $from, $filepath = null)
    {
        $this->classes[$key]['builder'] = $builderClass;
        $this->classes[$key]['construct'] = [$from, $filepath];
    }
    
    public function addConfiguration($key, Closure $config)
    {
        $this->configurations[$key] = $config;
    }
    
    public function build($key, $to)
    {
        $edifact = $this->instanceClass($key, $to);
        foreach ($this->configurations as $configKey => $config) {
            $edifact->addConfiguration($configKey, $config);
        }
        return $edifact;
    }

    private function instanceClass($key, $to)
    {
        if (isset($this->classes[$key])) {
            list($from, $filepath) = $this->classes[$key]['construct'];
            return new $this->classes[$key]['builder']($from, $to, $filepath);
        }

        throw new EdifactException("Class with Key '$key' not registered.");
    }
}
