<?php 

namespace Proengeno\Edifact\Validation;

use Exception;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;

class MessageValidator implements MessageValidatorInterface {
    private $segValidator;
    private $trueLinecount = 1;
    
    public function __construct($segValidator = null)
    {
        $this->segValidator = $segValidator ?: new SegmentValidator;
    }

    public function validate(EdifactMessageInterface $edifact)
    {
        $lines = $edifact->getSegments();
        foreach ($lines['messages'] as $messages) {
            foreach ($messages['body'] as $body) {
                $message = array_merge($lines['messageHeader'], $messages['bodyHeader'], $body, $messages['bodyFooter'], $lines['messageFooter']);
                $this->loopBlueprint($message, $edifact->getValidationBlueprint());
            }
        }

        return $this;
    }
    
    public function loopBlueprint($lines, $blueprint)
    {
        $blueprintCount = 0;
        for ($lineCount = 0; $lineCount < count($lines); $lineCount++) {
            // Gratz, Validation ist für die teilmenge erfolgreich durchgelaufen, gebe anzahl der durchläufe zurück
            if ($this->endOfBlueprint($blueprint, $blueprintCount)) {
                return $lineCount;
            }

            $this->validateSegment($lines[$lineCount]);
            $this->vaildateLine(@$lines[$lineCount], @$blueprint[$blueprintCount]);

            if ($this->segmentHasLoop($blueprint[$blueprintCount])) {
                $this->reLoop($lines, $blueprint, $lineCount, $blueprintCount);
            }

            $blueprintCount++;
            $this->trueLinecount++;
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

    private function reLoop($lines, $blueprint, &$lineCount, &$blueprintCount)
    {
        $cuttedLines = array_slice($lines, $lineCount+1);
        $lineCount += $this->loopBlueprint($cuttedLines, $blueprint[$blueprintCount]['segments']);

        if ($lines[$lineCount + 1]->name() == $blueprint[$blueprintCount]['name']) {
            $blueprintCount--;
        }
    }
    
    private function vaildateLine($line, $blueprint)
    {
        if ($line == null) {
            throw new Exception('Unerwartetes Edifact-Ende.');
        }
        if ($line->name() != $blueprint['name']) {
            if (isset($blueprint['name'])) {
                throw new Exception('Zeile ' . $this->trueLinecount . ': Unerwartetes Segement ' . @$line->name() . ', ' . $blueprint['name'] . ' erwartet.');
            }
            throw new Exception('Zeile ' . $this->trueLinecount . ': Unerwartetes Segement ' . @$line->name() . ', Ende erwartet.');
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
