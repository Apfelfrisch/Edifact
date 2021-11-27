<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Exceptions\EdifactException;

class Generic extends AbstractSegment
{
    public static function blueprint(): Elements
    {
        throw new EdifactException('Generic Segment has no Blueprint');
    }

    /**
     * @psalm-param list<list<string>> $valueArrays
     */
    public static function fromAttributes(string $name, array ...$valueArrays): self
    {
        $elements = new Elements;
        $elements->addValue('0', '0', $name);
        $i = 1;
        foreach ($valueArrays as $values) {
            $j = 1;
            foreach($values as $value) {
                $elements->addValue((string)$i, (string)$j, $value);
                $j++;
            }
            $i++;
        }

        return new self($elements);
    }

    public function validate(): void
    {
        return;
    }

    protected static function mapToBlueprint(Delimiter $delimiter, string $segLine): Elements
    {
        $inputElements = $delimiter->explodeElements($segLine);

        $elements = new Elements;

        for ($i = 0; $i < $_ = count($inputElements); $i++) {
            $inputComponents = $delimiter->explodeComponents($inputElements[$i]);

            for($j = 0; $j < $__ = count($inputComponents); $j++) {
                $elements->addValue((string)$i, (string)$j, $inputComponents[$j]);
            }
        }

        return $elements;
    }
}
