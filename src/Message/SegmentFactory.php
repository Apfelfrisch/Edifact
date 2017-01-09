<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class SegmentFactory
{
    protected $segmentNamespace;
    protected $delimiter;

    private $genericSegment;

    public function __construct($segmentNamespace, Delimiter $delimiter = null, $genericSegment = null)
    {
        $this->segmentNamespace = $segmentNamespace;
        $this->delimiter = $delimiter ?: new Delimiter;
        $this->genericSegment = $genericSegment;
    }

    public function fromSegline($segline)
    {
        return $this->instanciateSegment($this->getSegname($segline), 'fromSegLine', $segline);
    }

    public function fromAttributes($segmentName, $attributes = [], $method = 'fromAttributes')
    {
        return $this->instanciateSegment($segmentName, $method, $attributes);
    }

    private function instanciateSegment($segmentName, $method, $attributes)
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        $segment = $this->getSegmentClass($segmentName);

        if (is_callable([$segment, 'setBuildDelimiter'])) {
            call_user_func_array([$segment, 'setBuildDelimiter'], [$this->delimiter]);

            return call_user_func_array([$segment, $method], $attributes);
        }

        if ($this->genericSegment !== null) {
            call_user_func_array([$this->genericSegment, 'setBuildDelimiter'], [$this->delimiter]);

            return call_user_func_array([$this->genericSegment, $method], $attributes);
        }

        throw new ValidationException("Unknown Segment '" . $segmentName . "'");
    }


    private function getSegmentClass($segmentName)
    {
        return $this->segmentNamespace . '\\' . ucfirst(strtolower($segmentName));
    }

    private function getSegname($segLine)
    {
        return substr($segLine, 0, 3);
    }
}
