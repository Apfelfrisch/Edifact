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
        return call_user_func_array($segment . '::fromSegLine', [$segline, $this->delimiter]);
    }

    public function fromAttributes($segment, $attributes = [], $method = 'fromAttributes')
    {
        return call_user_func_array([$segment, $method], $attributes);
    }
}
