<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactBuilder
{
    private $prebuildConfig = [];
    private $postbuildConfig = [];
    private $classes = [];

    public function addBuilder($key, $builderClass, $from, $filepath = null)
    {
        $this->classes[$key]['builder'] = $builderClass;
        $this->classes[$key]['construct'] = [$from, $filepath];
    }
    
    public function addPrebuildConfig($key, Closure $config)
    {
        $this->prebuildConfig[$key] = $config;
    }

    public function addPostbuildConfig($key, Closure $config)
    {
        $this->postbuildConfig[$key] = $config;
    }
    
    public function build($key, $to, $filename = null)
    {
        $builder = $this->instanceClass($key, $to, $filename);
        foreach ($this->prebuildConfig as $configKey => $config) {
            $builder->addPrebuildConfig($configKey, $config);
        }
        foreach ($this->postbuildConfig as $configKey => $config) {
            $builder->addPostbuildConfig($configKey, $config);
        }
        return $builder;
    }

    private function instanceClass($key, $to, $filename)
    {
        if (isset($this->classes[$key])) {
            list($from, $filepath) = $this->classes[$key]['construct'];
            return new $this->classes[$key]['builder']($from, $to, $this->getFullpath($filepath, $filename));
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
