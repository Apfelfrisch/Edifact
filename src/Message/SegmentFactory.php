<?php

namespace Proengeno\Edifact\Message;

class SegmentFactory
{
    protected $messageClass;
    protected $delimiter;

    public function __construct($messageClass, Delimiter $delimiter = null)
    {
        $this->messageClass = $messageClass;
        $this->delimiter = $delimiter ?: new Delimiter;
    }

    public function fromSegline($segline)
    {
        $segment = $this->getSegmentClass($this->getSegname($segline));

        $this->setDelimiter($segment);

        return call_user_func_array($segment . '::fromSegLine', [$segline]);
    }

    public function fromAttributes($segmentName, $attributes = [], $method = 'fromAttributes')
    {
        $segment = $this->getSegmentClass($segmentName);

        $this->setDelimiter($segment);

        return call_user_func_array([$segment, $method], $attributes);
    }

    private function setDelimiter($segment)
    {
        call_user_func_array($segment . '::setBuildDelimiter', [$this->delimiter]);
    }

    private function getSegmentClass($segmentName)
    {
        return call_user_func([$this->messageClass, 'getSegmentClass'], $segmentName);
    }

    private function getSegname($segLine)
    {
        return substr($segLine, 0, 3);
    }
}
