<?php

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Templates\AbstractSegment;
use Proengeno\Edifact\Exceptions\EdifactException;

class GenericSegment extends AbstractSegment
{
    /**
     * @return void
     */
    public static function fromAttributes()
    {
        throw new EdifactException('Generic Segment can not be instanciate with "fromAttributes"');
    }

    public function validate()
    {
        return $this;
    }

    protected static function mapToBlueprint($segLine)
    {
        $i = 0;
        $elements = [];
        $inputDataGroups = static::getBuildDelimiter()->explodeSegments($segLine);
        foreach ($inputDataGroups as $inputDataGroup) {
            $inputElements = static::getBuildDelimiter()->explodeElements($inputDataGroups[$i]);
            $j = 0;
            foreach ($inputElements as $inputElement) {
                $elements[$i][$j] = $inputElement;
                $j++;
            }
            $i++;
        }

        return $elements;
    }
}
