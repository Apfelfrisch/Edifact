<?php

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\DataGroups;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\AbstractSegment;

class Segment extends AbstractSegment
{
    private static ?DataGroups $blueprint = null;

    public static function blueprint(): DataGroups
    {
        if (self::$blueprint === null) {
            self::$blueprint = (new DataGroups)
                ->addValue('A', 'A', 'M|an|3')
                ->addValue('B', 'B', 'O|an|3')
                ->addValue('C', '1', 'M|an|3')
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

    public static function fromAttributes(Delimiter $delimiter, $attribute): self
    {
        return new self(
            (new DataGroups)->addValue('A', 'A', $attribute),
            $delimiter
        );
    }

    public function dummyMethod()
    {
        return $this->elements->getValue('B', 'B');
    }
}
