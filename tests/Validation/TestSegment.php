<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Segments\AbstractSegment;

final class TestSegment extends AbstractSegment
{
    public static ?string $rule;

    public static function blueprint(): Elements
    {
        return (new Elements)
            ->addValue('TST', 'TST', 'M|a|3')
            ->addValue('1', '1', self::$rule);
    }

    public static function fromAttributes(string $value): self
    {
        return new self((new Elements)
            ->addValue('TST', 'TST', 'M|a|3')
            ->addValue('1', '1', $value)
        );
    }
}
