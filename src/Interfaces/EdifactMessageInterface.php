<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Interfaces\SegInterface;

interface EdifactMessageInterface
{
    public function findSegments($segmentName, $messageCount = 0, $bodyCount = 0);
    public function getCurrentSegment();
    public function getNextSegment();
    public function getPointerPosition();
    public function setPointerPosition($position);
    public function validate();
    public function __toString();
} 
