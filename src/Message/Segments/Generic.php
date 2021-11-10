<?php

namespace Proengeno\Edifact\Message\Segments;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Templates\AbstractSegment;
use Proengeno\Edifact\Exceptions\EdifactException;

class Generic extends AbstractSegment
{
    public static function blueprint(): DataGroups
    {
        throw new EdifactException('Generic Segment has no Blueprint');
    }

    public static function fromAttributes(): self
    {
        throw new EdifactException('Generic Segment can not be instanciate with "fromAttributes"');
    }

    public function validate(): void
    {
        return;
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): SegmentData
    {
        $inputDataGroups = $delimiter->explodeSegments($segLine);

        $dataCollection = new DataGroups;

        for ($i = 0; $i < $_ = count($inputDataGroups); $i++) {
            $inputElements = $delimiter->explodeElements($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                $dataCollection->addValue((string)$i, (string)$j, $inputElements[$j]);
            }
        }

        return new SegmentData($dataCollection, $delimiter);
    }
}
