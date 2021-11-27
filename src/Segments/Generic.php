<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Exceptions\EdifactException;

class Generic extends AbstractSegment
{
    public static function blueprint(): DataGroups
    {
        throw new EdifactException('Generic Segment has no Blueprint');
    }

    /**
     * @psalm-param list<list<string>> $valueArrays
     */
    public static function fromAttributes(string $name, array ...$valueArrays): self
    {
        $dataGroups = new DataGroups;
        $dataGroups->addValue('0', '0', $name);
        $i = 1;
        foreach ($valueArrays as $values) {
            $j = 1;
            foreach($values as $value) {
                $dataGroups->addValue((string)$i, (string)$j, $value);
                $j++;
            }
            $i++;
        }

        return new self($dataGroups);
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
            $inputElements = $delimiter->explodeComponents($inputDataGroups[$i]);

            for($j = 0; $j < $__ = count($inputElements); $j++) {
                $dataGroups->addValue((string)$i, (string)$j, $inputElements[$j]);
            }
        }

        return $dataGroups;
    }
}
