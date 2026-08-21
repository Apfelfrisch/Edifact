<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\AbstractSegmentTestSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class AbstractSegmentTest extends TestCase
{
    public function test_provide_segment_name(): void
    {
        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), 'A');

        static::assertSame('A', $segment->name());
    }

    public function test_array_casting(): void
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedArray = [
            'A' => ['A' => 'A'],
            'B' => ['B' => 'B'],
            'C' => [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'],
            'D' => ['D' => 'D'],
            'E' => ['E' => 'E'],
        ];

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), $givenString);

        static::assertEquals($expectedArray, $segment->toArray());
    }

    public function test_escaping_string(): void
    {
        $givenString = 'A+???:?+';

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), $givenString);

        static::assertSame('?:+', $segment->getValue('B', 'B'));
        static::assertSame('?:+', $segment->getValueFromPosition(1, 0));
    }

    public function test_replace_space_character(): void
    {
        $givenString = 'A+test_replace_space_char';

        $segment = AbstractSegmentTestSegment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', '.', '?', '_')),
            $givenString,
        );

        static::assertSame('test replace space char', $segment->getValue('B', 'B'));
        static::assertSame('test replace space char', $segment->getValueFromPosition(1, 0));
    }

    public function test_replace_decimal_point(): void
    {
        $givenString = 'A++1,23';

        $segment = AbstractSegmentTestSegment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', ',')),
            $givenString,
        );

        static::assertSame('1.23', $segment->getValue('C', '1'));
        static::assertSame('1.23', $segment->getValueFromPosition(2, 0));
    }

    public function test_value_is_numeric_when_the_blueprint_marks_it(): void
    {
        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), 'A+B+1');

        static::assertTrue($segment->isValueNumeric('C', '1'));
        static::assertFalse($segment->isValueNumeric('A', 'A'));
        static::assertFalse($segment->isValueNumeric('missing', 'missing'));
    }
}
