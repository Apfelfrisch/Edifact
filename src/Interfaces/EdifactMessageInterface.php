<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Interfaces\SegInterface;

interface EdifactMessageInterface
{
    public function getSegments();
    public function findSegments($segmentName, $messageCount = 0, $bodyCount = 0);
    public function validate();
    public function __toString();
} 
