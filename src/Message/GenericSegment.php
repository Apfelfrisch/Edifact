<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Templates\AbstractSegment;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Interfaces\SegInterface;

class GenericSegment extends AbstractSegment
{
    public static function fromSegline(string $segLine, ?Delimiter $delimiter = null): SegInterface
    {
        $delimiter ??= new Delimiter;

        $inputDataGroups = $delimiter->explodeSegments($segLine);

        $elements = [];

        for ($i = 0; $i < $_ = count($inputDataGroups); $i++) {
            $inputElements = $delimiter->explodeElements($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                // Force Php to string-cast the array keys
                $elements["_$i"]["_$j"] = $inputElements[$j];
            }
        }

        return new self($elements);
    }

    /**
     * @return void
     */
    public static function fromAttributes()
    {
        throw new EdifactException('Generic Segment can not be instanciate with "fromAttributes"');
    }

    public function validate(?SegValidatorInterface $validator = null): self
    {
        return $this;
    }
}
