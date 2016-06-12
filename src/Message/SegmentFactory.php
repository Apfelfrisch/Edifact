<?php 

namespace Proengeno\Edifact\Message;

class SegmentFactory
{
    protected $delimiter;
    
    public function __construct(Delimiter $delimiter = null)
    {
        $this->delimiter = $delimiter ?: new Delimiter;
    }
    
    public function fromSegline($segment, $segline)
    {
        call_user_func_array($segment . '::setBuildDelimiter', [$this->delimiter]);
        $segment = call_user_func_array($segment . '::fromSegLine', [$segline]);

        return $segment;
    }

    public function fromAttributes($segment, $attributes = [], $method = 'fromAttributes')
    {
        call_user_func_array($segment . '::setBuildDelimiter', [$this->delimiter]);
        $segment = call_user_func_array([$segment, $method], $attributes);

        return $segment;
    }
}
