<?php 

namespace Proengeno\Edifact\Interfaces;

use Closure;
use Iterator;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Interfaces\SegInterface;

interface MessageInterface extends Iterator
{
    public static function getSegmentClass($segmentName);
    public function getValidationBlueprint();
    public function addConfiguration($key, $config);
    public function getFilepath();
    public function getCurrentSegment();
    public function getNextSegment();
    public function findNextSegment($searchSegment);
    public function pinPointer();
    public function jumpToPinnedPointer();
    public function validate();
    public function getDelimiter();
    public function __toString();
} 
