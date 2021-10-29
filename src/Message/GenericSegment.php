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

    public function validate(): self
    {
        return $this;
    }

    /**
     * @return array<string, array<string, null|string>>
     */
    protected static function mapToBlueprint(string $segLine): array
    {
        $inputDataGroups = static::getBuildDelimiter()->explodeSegments($segLine);

        $elements = [];

        for ($i = 0; $i < $_ = count($inputDataGroups); $i++) {
            $inputElements = static::getBuildDelimiter()->explodeElements($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                // Force Php to string-cast the array keys
                $elements["_$i"]["_$j"] = $inputElements[$j];
            }
        }

        return $elements;
    }
}
