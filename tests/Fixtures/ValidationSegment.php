<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Fixtures;

use Apfelfrisch\Edifact\Segment\AbstractSegment;
use Apfelfrisch\Edifact\Segment\Elements;

final class ValidationSegment extends AbstractSegment
{
    public static null|string $ruleOne = null;

    public static null|string $ruleTwo = 'O|a|3';

    public static function blueprint(): Elements
    {
        return new Elements()
            ->addValue('UNH', 'UNH', 'M|a|3')
            ->addValue('1', '1', self::$ruleOne)
            ->addValue('1', '2', self::$ruleTwo);
    }

    public static function fromAttributes(null|string $valueOne = null, null|string $valueTwo = null): self
    {
        return new self(
            new Elements()
                ->addValue('UNH', 'UNH', 'M|a|3')
                ->addValue('1', '1', $valueOne)
                ->addValue('1', '1', $valueTwo),
        );
    }
}
