<?php 

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Message\SegmentRegister;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Interfaces\EdifactMessageInterface;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

abstract class MessageCore implements EdifactMessageInterface
{
    protected static $firstBodySegment;
    protected static $validationBlueprint;

    private $segments;
    private $validator;
    
    private function __construct(array $segments, $validator)
    {
        $this->segments = $segments;
        $this->validator = $validator;
    }
    
    public static function fromString($string, MessageValidatorInterface $validator = null)
    {
        $delimiter = Delimiter::setFromEdifact($string);

        $nestedCounter = -1;
        $itemCount = -1;
        foreach ($delimiter->explodeMessage($string) as $segLine) {
            $segment = self::getSegmentObject($segLine, $delimiter);
            if (self::isNewNestedMessage($segment)) {
                $nestedCounter++;
                $itemCount = -1;
            }
            $itemCount = self::countItem($segment, $itemCount);

            self::merge($segments, $segment, $nestedCounter, $itemCount);
        }

        return new static($segments, $validator ?: new MessageValidator);
    }
    
    public function getSegments()
    {
        return $this->segments;
    }

    public function getFlatSegments(array $array) {
        $flattendArray = [];
        array_walk_recursive($array, function($item) use (&$flattendArray) { 
            $flattendArray[] = $item; 
        });

        return $flattendArray;
    }

    public function getValidationBlueprint()
    {
        return static::$validationBlueprint;
    }

    public function findSegments($segmentSearch, $messageCount = null, $bodyCount = null)
    {
        $results = [];
        $arrayPointer = $this->segments;

        if ($messageCount !== null && $bodyCount !== null) {
            if (isset($this->segments['messages'][$messageCount]['body'][$bodyCount])) {
                $arrayPointer = $this->segments['messages'][$messageCount]['body'][$bodyCount];
            }
        } elseif ($messageCount !== null) {
            if (isset($this->segments['messages'][$messageCount])) {
                $arrayPointer = $this->segments['messages'][$messageCount];
            }
        }

        array_walk_recursive($arrayPointer, function($segment) use ($segmentSearch, &$results) {
            if (SegmentRegister::getClassname($segmentSearch) == get_class($segment)) {
                $results[] = $segment;
            }
        });

        return $results;
    }

    public function validate()
    {
        $this->validator->validate($this);

        return $this;
    }

    public function __toString()
    {
        return implode("'", $this->getFlatSegments($this->segments)) . "'";
    }

    private static function merge(&$segments, $segment, $nestedCounter, &$itemCount)
    {
        if ($nestedCounter < 0) {
            $segments['messageHeader'][] = $segment;
            return;
        }

        if ($segment->name() == 'UNZ') {
            $segments['messageFooter'][] = $segment;
            return;
        }

        if ($segment->name() == 'UNT') {
            $segments['messages'][$nestedCounter]['bodyFooter'][] = $segment;
            return;
        }

        if ($itemCount < 0) {
            $segments['messages'][$nestedCounter]['bodyHeader'][] = $segment;
            return;
        }

        $segments['messages'][$nestedCounter]['body'][$itemCount][] = $segment;
    }


    private static function getSegmentObject($segLine, $delimiter)
    {
        return call_user_func_array(SegmentRegister::getClassname(self::getSegname($segLine)) . '::fromSegLine', [$segLine, $delimiter]);
    }

    private static function getSegname($segLine) 
    {
        return substr($segLine, 0, 3);
    }

    private static function isNewNestedMessage($line)
    {
        return $line->name() == 'UNH';
    }

    private static function countItem($line, $counter)
    {
        if ($line->name() == static::$firstBodySegment) {
            return ++$counter;
        }
        return $counter;
    }
}
