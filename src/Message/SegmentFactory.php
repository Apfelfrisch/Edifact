<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Exceptions\ValidationException;

class SegmentFactory
{
    protected $segmentNamespace;
    protected $delimiter;

    public function __construct($segmentNamespace, Delimiter $delimiter = null)
    {
        $this->segmentNamespace = $segmentNamespace;
        $this->delimiter = $delimiter ?: new Delimiter;
    }

    public function fromSegline($segline)
    {
        $segment = $this->getSegmentClass($this->getSegname($segline));

        if (is_callable([$segment, 'setBuildDelimiter'])) {
            $segment::setBuildDelimiter($this->delimiter);

            return $segment::fromSegLine($segline);
        }

        throw new ValidationException("Unknown Segment '" . $this->getSegname($segline) . "'");
    }

    public function fromAttributes($segmentName, $attributes = [], $method = 'fromAttributes')
    {
        $segment = $this->getSegmentClass($segmentName);

        $segment::setBuildDelimiter($this->delimiter);

        return call_user_func_array([$segment, $method], $attributes);
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
