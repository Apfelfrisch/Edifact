<?php

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\Segment\AbstractSegment;
use Apfelfrisch\Edifact\Segment\Elements;

class AbstractSegmentTestSegment extends AbstractSegment
{
    private static ?Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new Elements())
                ->addValue('A', 'A', 'M|an|3')
                ->addValue('B', 'B', 'O|an|3')
                ->addValue('C', '1', 'M|n|3')
                ->addValue('C', '2', 'M|an|3')
                ->addValue('C', '3', 'O|an|3')
                ->addValue('C', '4', 'M|an|3')
                ->addValue('C', '5', 'M|an|3')
                ->addValue('D', 'D', 'O|an|3')
                ->addValue('E', 'E', 'O|an|3')
                ->addValue('F', 'F', 'O|an|3');
        }

        return self::$blueprint;
    }

    public static function fromAttributes(string $a, string|null $b = null, string|null $c = null): self
    {
        return new self(
            (new Elements())->addValue('A', 'A', $a)
                ->addValue('B', 'B', $b)
                ->addValue('C', '1', $c)
        );
    }

    public function dummyMethod(): ?string
    {
        return $this->elements->getValue('B', 'B');
    }
}
