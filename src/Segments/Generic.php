<?php

namespace Apfelfrisch\Edifact\Segments;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\UnaSegment;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\SeglineParser;

class Generic extends AbstractSegment
{
    public static function blueprint(): Elements
    {
        throw new EdifactException('Generic Segment has no Blueprint');
    }

    public static function fromSegLine(SeglineParser $parser, string $segLine): static
    {
        $segment = new static($parser->parse($segLine));
        $segment->setUnaSegment($parser->getUnaSegment());

        return $segment;
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
}
