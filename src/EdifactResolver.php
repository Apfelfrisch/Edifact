<?php

namespace Proengeno\Edifact;

use Closure;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactResolver
{
    private $edifactFile;
    private $allocationRules = [];
    private $postbuildConfig = [];

    public function addAllocationRule($edifactClass, $allocationRules)
    {
        $this->allocationRules[$edifactClass] = $allocationRules;
    }

    public function addPostbuildConfig($key, $config)
    {
        $this->postbuildConfig[$key] = $config;
    }

    public function fromFile($filepath)
    {
        $this->edifactFile = new EdifactFile($filepath);

        return new Message($this->applyPostbuildConfig($this->resolvEdifactObject()));
    }
    
    public function fromString($string)
    {
        $this->edifactFile = new EdifactFile('php://temp', 'w+');
        $this->edifactFile->writeAndRewind($string);

        $message = $this->applyPostbuildConfig($this->resolvEdifactObject());
        return new Message($message);
    }

    private function applyPostbuildConfig($edifactObject)
    {
        foreach ($this->postbuildConfig as $configKey => $config) {
            $edifactObject->addConfiguration($configKey, $config);
        }
        return $edifactObject;
    }

    // Need Refactoring
    private function resolvEdifactObject()
    {
        $tmpAllocationRules = $this->allocationRules;
        while ($segment = $this->edifactFile->getSegment()) {
            $segmenName = substr($segment, 0, 3);

            foreach ($tmpAllocationRules as $edifactClass => $rules) {
                if (isset($rules[$segmenName])) {
                    if (preg_match($rules[$segmenName], $segment)) {
                        unset($tmpAllocationRules[$edifactClass][$segmenName]);
                        if (count($tmpAllocationRules[$edifactClass]) == 0) {
                            return new $edifactClass($this->edifactFile);
                        }
                    }
                }
            }
        }
        throw new EdifactException('Could find Message, for given rules');
    }
}
