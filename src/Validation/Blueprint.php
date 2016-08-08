<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Exceptions\ValidationException;

class Blueprint
{
    private $loopDeep = 0;
    private $blueprint = [];
    private $loopLevelCount = [];
    private $blueprintCount = [];
    private $loopIsNecessary = true;
    private $optinalSegmentException;
    
    public function __construct($blueprint)
    {
        $this->flattenBlueprint($blueprint);
    }

    public function validate($segment)
    {
        validationStart:
        if ($this->unnecessarySegmentIsMissing($segment) || $this->unnecessaryLoopIsMissing($segment)) {
            $this->countUpBlueprint();
        }
        
        if ($this->startOfLoop()) {
            $this->nextLoop();
        } elseif ($this->startOfReLoop($segment)) {
            $this->reLoop();
        } 

        if ($this->endOfLoop()) {
            $this->previosLoop();
            $this->validate($segment);
            return;
        }

        if ($segment->name() == $this->getBlueprintAttribute('name')) {
            try {
                $this->validateBlueprintTemplates($segment);
            } catch (ValidationException $e) {
                if ($this->getBlueprintAttribute('necessity') != 'O') {
                    throw $e;
                }
                $this->countUpBlueprint();
                $this->optinalSegmentException = $e;
                goto validationStart;
            }
            $this->loopIsNecessary = true;
            $this->countUpBlueprint();
            return;
        }

        if ($this->optinalSegmentException) {
            throw $this->optinalSegmentException;
        }
        throw ValidationException::unexpectedSegment('', @$segment->name(), $this->getBlueprintAttribute('name'));
    }

    private function validateBlueprintTemplates($segment)
    {
        if ($this->getBlueprintAttribute('templates')) {
            foreach ($this->getBlueprintAttribute('templates') as $segmendMethod => $suggestions) {
                if (in_array($segment->$segmendMethod(), $suggestions)) {
                    continue;
                }

                throw ValidationException::illegalContent('', $segment->name(), $segment->$segmendMethod(), implode('" | "', $suggestions));
            }
        }
    }

    private function flattenBlueprint($blueprint, $nestedDeep = 0, $levelCount = 0)
    {
        $levelCounter = 0;
        foreach ($blueprint as $blueprintRow) {
            if (isset($blueprintRow['segments'])) {
                $this->flattenBlueprint($blueprintRow['segments'], $nestedDeep + 1, $levelCounter);
                $levelCounter++;
            }
            $this->blueprint[$nestedDeep][$levelCount][] = $blueprintRow;
        }
    }

    private function unnecessarySegmentIsMissing($segment)
    {
        return $this->getBlueprintAttribute('name') != 'LOOP'
            && $this->getBlueprintAttribute('necessity') == 'O'
            && $segment->name() != $this->getBlueprintAttribute('name');
    }

    private function unnecessaryLoopIsMissing($segment)
    {
        return $this->getBlueprintAttribute('name') == 'LOOP'
            && $segment->name() == $this->getBlueprintAttribute('name', $this->getBlueprintCount() + 1)
            && $this->getBlueprintAttribute('necessity') == 'O';
    }

    private function startOfLoop()
    {
        return $this->getBlueprintAttribute('name') == 'LOOP';
    }

    private function reLoop()
    {
        $this->blueprintCount[$this->loopDeep] = 0;
        unset($this->loopLevelCount[$this->loopDeep + 1]);
    }

    private function endOfLoop()
    {
        $segmentCount = isset($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()]) 
            ? count($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()])
            : 0;
        return $this->getBlueprintCount() >= $segmentCount;
    }

    private function previosLoop()
    {
        $this->loopDeep--;
        $this->countUpBlueprint();
    }

    private function startOfReLoop($segment)
    {
        if ($this->endOfLoop() && $segment->name() == $this->getBlueprintAttribute('name', 0)) {
            return true;
        }
        return false;
    }

    private function nextLoop()
    {
        $this->loopDeep++;
        $this->countUpLevelLoop();
        $this->blueprintCount[$this->loopDeep] = 0;
        unset($this->loopLevelCount[$this->loopDeep + 1]);
    }

    private function countUpLevelLoop()
    {
        if (!isset($this->loopLevelCount[$this->loopDeep])) {
            $this->loopLevelCount[$this->loopDeep] = -1;
        }
        $this->loopLevelCount[$this->loopDeep]++;
    }
    
    private function getLevelLoopCount()
    {
        if (!isset($this->loopLevelCount[$this->loopDeep])) {
            $this->loopLevelCount[$this->loopDeep] = 0;
        }

        return $this->loopLevelCount[$this->loopDeep];
    }

    private function getBlueprintAttribute($attribute, $blueprintCount = null)
    {
        if ($blueprintCount === null) {
            $blueprintCount = $this->getBlueprintCount();
        }
        if (isset($this->blueprint[$this->loopDeep][$this->getLevelLoopCount()][$blueprintCount][$attribute])) {
            return $this->blueprint[$this->loopDeep][$this->getLevelLoopCount()][$blueprintCount][$attribute];
        }        
        return null;
    }

    private function getBlueprintCount()
    {
        if (!isset($this->blueprintCount[$this->loopDeep])) {
            $this->blueprintCount[$this->loopDeep] = 0;
        }
        return $this->blueprintCount[$this->loopDeep];
    }

    private function countUpBlueprint()
    {
        if (!isset($this->blueprintCount[$this->loopDeep])) {
            $this->blueprintCount[$this->loopDeep] = 1;
        }
        $this->blueprintCount[$this->loopDeep] ++;
    }
}
