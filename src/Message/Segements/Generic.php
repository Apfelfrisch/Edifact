<?php

namespace Proengeno\Edifact\Message\Segements;

use Proengeno\Edifact\Message\DataGroupCollection;
use Proengeno\Edifact\Templates\AbstractSegment;
use Proengeno\Edifact\Exceptions\EdifactException;

class Generic extends AbstractSegment
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

    protected static function mapToBlueprint(string $segLine): DataGroupCollection
    {
        $inputDataGroups = static::getBuildDelimiter()->explodeSegments($segLine);

        $dataCollection = new DataGroupCollection;

        for ($i = 0; $i < $_ = count($inputDataGroups); $i++) {
            $inputElements = static::getBuildDelimiter()->explodeElements($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                $dataCollection->addValue((string)$i, (string)$j, $inputElements[$j]);
            }
        }

        return $dataCollection ;
    }
}
