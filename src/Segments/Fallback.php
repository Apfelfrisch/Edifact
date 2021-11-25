<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Exceptions\EdifactException;

class Fallback extends AbstractSegment
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

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): DataGroups
    {
        $inputDataGroups = $delimiter->explodeDataGroups($segLine);

        $dataGroups = new DataGroups;

        for ($i = 0; $i < $_ = count($inputDataGroups); $i++) {
            $inputElements = $delimiter->explodeElements($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                $dataGroups->addValue((string)$i, (string)$j, $inputElements[$j]);
            }
        }

        return $dataGroups;
    }
}
