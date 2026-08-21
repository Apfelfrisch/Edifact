<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Validation;

use Apfelfrisch\Edifact\Segment\Elements;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Validation\Failure;
use Apfelfrisch\Edifact\Validation\SegmentValidator;

final class SegmentValidatorTest extends TestCase
{
    public function test_elements_beyond_the_blueprint_are_unknown(): void
    {
        $validator = new SegmentValidator();

        $blueprint = new Elements()->addValue('UNH', 'UNH', 'M|an|3');
        $data = new Elements()
            ->addValue('UNH', 'UNH', 'UNH')
            ->addValue('X', '1', 'x')
            ->addValue('X', '2', 'y');

        $failures = iterator_to_array($validator->validate($blueprint, $data), false);

        static::assertCount(2, $failures);
        static::assertSame(Failure::UNKNOWN_ELEMENT, $failures[0]->type);
        static::assertSame(Failure::UNKNOWN_ELEMENT, $failures[1]->type);
    }

    public function test_missing_components_without_rules_are_skipped(): void
    {
        $validator = new SegmentValidator();

        $blueprint = new Elements()
            ->addValue('UNH', 'UNH', 'M|an|3')
            ->addValue('A', '1', 'M|an|..3')
            ->addValue('A', '2', null)
            ->addValue('A', '3', 'M|an|..3')
            ->addValue('A', '4', 'M|an|..3');
        $data = new Elements()
            ->addValue('UNH', 'UNH', 'UNH')
            ->addValue('A', '1', 'x');

        $failures = iterator_to_array($validator->validate($blueprint, $data), false);

        static::assertCount(2, $failures);
        static::assertSame(Failure::MISSING_COMPONENT, $failures[0]->type);
        static::assertSame(Failure::MISSING_COMPONENT, $failures[1]->type);
    }

    public function test_missing_elements_report_every_needed_component(): void
    {
        $validator = new SegmentValidator();

        $blueprint = new Elements()
            ->addValue('UNH', 'UNH', 'M|an|3')
            ->addValue('B', '1', null)
            ->addValue('B', '2', 'M|an|..3')
            ->addValue('B', '3', 'M|an|..3');
        $data = new Elements()->addValue('UNH', 'UNH', 'UNH');

        $failures = iterator_to_array($validator->validate($blueprint, $data), false);

        static::assertCount(2, $failures);
        static::assertSame(Failure::MISSING_COMPONENT, $failures[0]->type);
        static::assertSame(Failure::MISSING_COMPONENT, $failures[1]->type);
    }

    public function test_a_mutated_blueprint_invalidates_the_validator_cache(): void
    {
        $validator = new SegmentValidator();

        $blueprint = new Elements()
            ->addValue('UNH', 'UNH', 'M|an|3')
            ->addValue('A', '1', 'M|an|..3');
        $data = new Elements()
            ->addValue('UNH', 'UNH', 'UNH')
            ->addValue('A', '1', 'abc');

        static::assertSame([], iterator_to_array($validator->validate($blueprint, $data), false));

        $blueprint->addValue('B', '1', 'M|an|..3');

        $failures = iterator_to_array($validator->validate($blueprint, $data), false);

        static::assertCount(1, $failures);
        static::assertSame(Failure::MISSING_COMPONENT, $failures[0]->type);
    }
}
