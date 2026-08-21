<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\Segment\AbstractSegment;
use Apfelfrisch\Edifact\Segment\Elements;

final class AbstractSegmentTestSegment extends AbstractSegment
{
    private static null|Elements $blueprint = null;

    public static function blueprint(): Elements
    {
        return self::$blueprint ??= new Elements()
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

    public static function fromAttributes(string $a, null|string $b = null, null|string $c = null): self
    {
        return new self(
            new Elements()
                ->addValue('A', 'A', $a)
                ->addValue('B', 'B', $b)
                ->addValue('C', '1', $c),
        );
    }

    public function dummyMethod(): null|string
    {
        return $this->elements->getValue('B', 'B');
    }
}
