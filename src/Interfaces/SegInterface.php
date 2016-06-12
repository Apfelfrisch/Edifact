<?php 

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\Delimiter;

interface SegInterface {
    public static function fromSegLine($segLine);
    public static function setBuildDelimiter(Delimiter $delimiter);
    public static function getBuildDelimiter();
    public function getDelimiter();
    public function name();
    public function validate();
    public function __toString();
} 
