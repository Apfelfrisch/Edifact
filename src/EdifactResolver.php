<?php

namespace Proengeno\Edifact;

class EdifactResolver
{
    private $allocationRules;
    private $rulesMatchcount;
    public function addAllocationRule($edifactClass, $allocationRules)
    {
        $this->allocationRules[$edifactClass] = $allocationRules;
        $this->rulesMatchcount[$edifactClass] = 0;

        return $this;
    }

    public function fromFile($filepath)
    {
        $this->edifactFile = new EdifactFile($filepath);

        return $this->resolveEdifactClass();
    }
    
    public function fromString($string)
    {
        $this->edifactFile = new EdifactFile('php://temp', 'w+');
        $this->edifactFile->writeAndRewind($string);
        
        return $this->resolveEdifactClass();
    }

    // Need Refactoring
    private function resolveEdifactClass()
    {
        $tmpAllocationRules = $this->allocationRules;
        while ($segment = $this->edifactFile->getSegment()) {
            $segmenName = substr($segment, 0, 3);

            foreach ($tmpAllocationRules as $edifactClass => $rules) {
                if (isset($rules[$segmenName])) {
                    if (preg_match($rules[$segmenName], $segment)) {
                        unset($tmpAllocationRules[$edifactClass][$segmenName]);
                        if (count($tmpAllocationRules[$edifactClass]) == 0 ) {
                            return new $edifactClass($this->edifactFile);
                        }
                    }
                }
            }
        }
    }
    
}
