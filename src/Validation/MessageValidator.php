<?php 

namespace Proengeno\Edifact\Validation;

use Exception;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;

class MessageValidator implements MessageValidatorInterface 
{
    private $segValidator;
    private $trueLinecount = 1;
    
    public function __construct($segValidator = null)
    {
        $this->segValidator = $segValidator ?: new SegmentValidator;
    }

    public function validate(EdifactMessageInterface $edifact)
    {
        $this->loop($edifact, $edifact->getValidationBlueprint());

        return $this;
    }
    
    public function loop($edifact, $blueprint)
    {
        $lineCount = 0;
        $blueprintCount = 0;
        while ($line = $edifact->getNextSegment()) {
            // Gratz, Validation ist für die teilmenge erfolgreich durchgelaufen, gebe anzahl der durchläufe zurück
            if ($this->endOfBlueprint($blueprint, $blueprintCount)) {
                return $lineCount;
            }

            $this->validateSegment($line);
            $this->validateAgainstBlueprint($line, @$blueprint[$blueprintCount]);

            if ($this->segmentHasLoop($blueprint[$blueprintCount])) {
                $this->reLoop($edifact, $blueprint, $lineCount, $blueprintCount);
            }

            $blueprintCount++;
            $this->trueLinecount++;
            $this->lastPosition = $edifact->getPointerPosition();
        }
    }

    private function endOfBlueprint($blueprint, $blueprintCount)
    {
        return !isset($blueprint[$blueprintCount]);
    }

    private function segmentHasLoop($segment)
    {
        return isset($segment['maxLoops']);
    }

    private function reLoop($edifact, $blueprint, &$lineCount, &$blueprintCount)
    {
        $lineCount += $this->loop($edifact, $blueprint[$blueprintCount]['segments']);
        //if ($edifact->getNextSegment()->name() == $blueprint[$blueprintCount]['name']) {
        //    $blueprintCount--;
        //}
        $edifact->setPointerPosition($this->lastPosition);
    }
    
    private function validateAgainstBlueprint($segment, $blueprint)
    {
        if ($segment == null) {
            throw new Exception('Unerwartetes Edifact-Ende.');
        }
        $this->validateBlueprintNames($segment, $blueprint);
        $this->validateBlueprintTemplates($segment, $blueprint);
    }

    private function validateBlueprintNames($segment, $blueprint)
    {
        if ($segment->name() != $blueprint['name']) {
            if (isset($blueprint['name'])) {
                throw new Exception('Zeile ' . $this->trueLinecount . ': Unerwartetes Segement ' . @$segment->name() . ', ' . $blueprint['name'] . ' erwartet.');
            }
            throw new Exception('Zeile ' . $this->trueLinecount . ': Unerwartetes Segement ' . @$segment->name() . ', Ende erwartet.');
        }
    }

    private function validateBlueprintTemplates($segment, array $blueprint)
    {
        if (isset($blueprint['templates'])) {
            foreach ($blueprint['templates'] as $segmendMethod => $suggestions) {
                if (!in_array($segment->$segmendMethod(), $suggestions)) {
                    $message = 'Zeile ' . $this->trueLinecount
                        . ', Segment ' . $segment->name()
                        . ', enthält unerlaubten Inhalt: "' . $segment->$segmendMethod() . '"'
                        . '. Erlaubt ist ("' . implode('" | "', $suggestions). '")';
                    throw new Exception($message);
                }
            }
        }
    }
    
    private function validateSegment(SegInterface $segment)
    {
        try {
            $segment->validate($this->segValidator);
        } catch (Exception $e) {
            throw new Exception('Zeile ' . $this->trueLinecount . ', Segment ' . $segment->name() . ', ' . $e->getMessage());
        }
    }
}
