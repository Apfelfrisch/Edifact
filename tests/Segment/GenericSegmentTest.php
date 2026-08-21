<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Test\TestCase;

final class GenericSegmentTest extends TestCase
{
    public function test_instantiate_from_static_constructor(): void
    {
        $segment = GenericSegment::fromAttributes('UNB', ['1', '2'], ['3']);

        static::assertSame('UNB', $segment->name());
        static::assertSame('1', $segment->getValue('1', '1'));
        static::assertSame('2', $segment->getValue('1', '2'));
        static::assertSame('3', $segment->getValue('2', '1'));
    }

    public function test_instantiate_from_segline(): void
    {
        $segment = GenericSegment::fromSegLine(new SeglineParser(), 'UNB+1:2+3');

        static::assertSame('UNB', $segment->name());
        static::assertSame('UNB', $segment->getValueFromPosition(0, 0));
        static::assertSame('1', $segment->getValueFromPosition(1, 0));
        static::assertSame('2', $segment->getValueFromPosition(1, 1));
        static::assertSame('3', $segment->getValueFromPosition(2, 0));
    }

    public function test_values_are_never_numeric(): void
    {
        $segment = GenericSegment::fromAttributes('MOA', ['1.5']);

        static::assertFalse($segment->isValueNumeric('1', '1'));
    }

    public function test_array_casting(): void
    {
        $segment = GenericSegment::fromAttributes('UNB', ['1', '2'], ['3']);

        static::assertSame(
            [
                0 => [0 => 'UNB'],
                1 => [1 => '1', 2 => '2'],
                2 => [1 => '3'],
            ],
            $segment->toArray(),
        );
    }
}
