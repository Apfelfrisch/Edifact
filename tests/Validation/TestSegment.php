<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\AbstractSegment;

final class TestSegment extends AbstractSegment
{
    public static string|null $ruleOne;
    public static string|null $ruleTwo = 'O|a|3';

    public static function blueprint(): Elements
    {
        return (new Elements)
            ->addValue('TST', 'TST', 'M|a|3')
            ->addValue('1', '1', self::$ruleOne)
            ->addValue('1', '2', self::$ruleTwo);
    }

    public static function fromAttributes(string|null $valueOne = null, string|null $valueTwo = null): self
    {
        return new self((new Elements)
            ->addValue('TST', 'TST', 'M|a|3')
            ->addValue('1', '1', $valueOne)
            ->addValue('1', '1', $valueTwo)
        );
    }
}
