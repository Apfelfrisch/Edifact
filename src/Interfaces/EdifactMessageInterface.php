<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Interfaces\SegInterface;

interface EdifactMessageInterface
{
    public function getCurrentSegment();
    public function getNextSegment();
    public function findNextSegment($searchSegment);
    public function getPointerPosition();
    public function setPointerPosition($position);
    public function validate();
    public function __toString();
} 
