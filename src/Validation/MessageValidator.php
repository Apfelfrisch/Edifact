<?php 

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Exceptions\ValidationException;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

/*
 * Todo: Klasse komplett neuschreiben, die ist Mist
 */
class MessageValidator implements MessageValidatorInterface 
{
    private $segValidator;
    private $lineCount = 1;
    private $reLoopCount = [];
    
    public function __construct($segValidator = null)
    {
        $this->segValidator = $segValidator ?: new SegmentValidator;
    }

    public function validate(EdifactMessageInterface $edifact)
    {
        $this->loop($edifact, $edifact->getValidationBlueprint());
        $segment = $edifact->getCurrentSegment();
        if ($segment->name() != 'UNZ' || $edifact->getNextSegment()) {
            throw new ValidationException('Zeile ' . $this->lineCount . ': Unerwartetes Segement ' . @$segment->name() . ', Ende erwaret.');
        }

        return $this;
    }
    
    public function loop($edifact, $blueprint)
    {
        $blueprintCount = 0;
        while ($line = $edifact->getNextSegment()) {
            // Gratz, Validation ist für die teilmenge erfolgreich durchgelaufen, gebe anzahl der durchläufe zurück
            if ($this->endOfBlueprint($blueprint, $blueprintCount)) {
                return;
            }
            $this->validateSegment($line);
            $this->validateAgainstBlueprint($line, @$blueprint[$blueprintCount]);
            
            if ($this->isSubSegmentReloop($edifact, $blueprint, $blueprintCount)) {
                $this->reLoopSubSegments($edifact, $blueprint, $blueprintCount);
            } elseif ($this->isSingleSegmentReloop($edifact, $blueprint, $blueprintCount)) {
                $blueprintCount--;
            }

            $this->lineCount++;
            $blueprintCount++;
            $this->lastPosition = $edifact->getPointerPosition();
        }
    }

    private function endOfBlueprint($blueprint, $blueprintCount)
    {
        return !isset($blueprint[$blueprintCount]);
    }

    private function isSubSegmentReloop($edifact, $blueprint, $blueprintCount)
    {
        if (!$this->segmentIsLoopable($blueprint, $blueprintCount) ) {
            return false;
        }
        if (!isset($blueprint[$blueprintCount]['segments'])) {
            return false;
        }
        return true;
    }

    private function isSingleSegmentReloop($edifact, $blueprint, $blueprintCount)
    {
        if (!$this->segmentIsLoopable($blueprint, $blueprintCount) ) {
            return false;
        }
        if (!isset($blueprint[$blueprintCount]['segments'])) {
            $position = $edifact->getPointerPosition();
            if ($edifact->getNextSegment()->name() == $blueprint[$blueprintCount]['name']) {
                $edifact->setPointerPosition($position);
                // Hier sollten wir nicht hochzählen, den Methodenname suggeriert etwas anderes
                isset($this->reLoopCount[$blueprintCount]) ? $this->reLoopCount[$blueprintCount] ++ : $this->reLoopCount[$blueprintCount] = 0;
                return true;
            } else {
                $edifact->setPointerPosition($position);
                $this->reLoopCount[$blueprintCount] = 0;
            }
        }

        return false;
    }

    private function reLoopSubSegments($edifact, $blueprint, &$blueprintCount)
    {
        $this->loop($edifact, $blueprint[$blueprintCount]['segments']);
        if ($edifact->getCurrentSegment()->name() == $blueprint[$blueprintCount]['name']) {
            isset($this->reLoopCount[$blueprintCount]) ? $this->reLoopCount[$blueprintCount] ++ : $this->reLoopCount[$blueprintCount] = 0;
            $blueprintCount --;
        } else {
            $this->reLoopCount[$blueprintCount] = 0;
        }

        $edifact->setPointerPosition($this->lastPosition);
    }

    private function segmentIsLoopable($blueprint, $blueprintCount)
    {
        $segment = $blueprint[$blueprintCount];
        if (!isset($segment['maxLoops']) && !isset($segment['segments']) ) {
            return false;
        }
        if ($this->checkReLoopCount($blueprintCount, $segment)) {
            return true;
        }
        throw new ValidationException(
            'Zeile ' . $this->lineCount . ', Segment ' . $segment['name'] . ', maximale Schleifendurchläufe (' . $segment['maxLoops'] . ') ereicht.'
        );
    }

    private function checkReLoopCount($blueprintCount, $segment)
    {
        $maxLoops = isset($segment['maxLoops']) ? $segment['maxLoops'] : 1;
        if (!isset($this->reLoopCount[$blueprintCount])) {
            return true;
        }
        if ($this->reLoopCount[$blueprintCount] < $maxLoops) {
            return true;
        }
        return false;
    }

    private function validateAgainstBlueprint($segment, $blueprint)
    {
        if ($segment == null) {
            throw new ValidationException('Unerwartetes Edifact-Ende.');
        }
        $this->validateBlueprintNames($segment, $blueprint);
        $this->validateBlueprintTemplates($segment, $blueprint);
    }

    private function validateBlueprintNames($segment, $blueprint)
    {
        if ($segment->name() != $blueprint['name']) {
            if (isset($blueprint['name'])) {
                throw new ValidationException('Zeile ' . $this->lineCount . ': Unerwartetes Segement ' . @$segment->name() . ', ' . $blueprint['name'] . ' erwartet.');
            }
            throw new ValidationException('Zeile ' . $this->lineCount . ': Unerwartetes Segement ' . @$segment->name() . ', Ende erwartet.');
        }
    }

    private function validateBlueprintTemplates($segment, array $blueprint)
    {
        if (isset($blueprint['templates'])) {
            foreach ($blueprint['templates'] as $segmendMethod => $suggestions) {
                if (!in_array($segment->$segmendMethod(), $suggestions)) {
                    $message = 'Zeile ' . $this->lineCount
                        . ', Segment ' . $segment->name()
                        . ', enthält unerlaubten Inhalt: "' . $segment->$segmendMethod() . '"'
                        . '. Erlaubt ist ("' . implode('" | "', $suggestions). '")';
                    throw new ValidationException($message);
                }
            }
        }
    }
    
    private function validateSegment(SegInterface $segment)
    {
        try {
            $segment->validate($this->segValidator);
        } catch (SegValidationException $e) {
            throw new ValidationException('Zeile ' . $this->lineCount . ', Segment ' . $segment->name() . ', ' . $e->getMessage());
        }
    }
}
