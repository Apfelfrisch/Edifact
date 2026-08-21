<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Formatter;

use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\AbstractSegmentTestSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class EdifactFormatterTest extends TestCase
{
    public function test_formatting_one_segment_to_edifact_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedString = $givenString . $unaSegment->segmentTerminator();

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), $givenString);

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format($segment));
    }

    public function test_formatting_multiple_segments_to_edifact_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $expectedString = '';

        $segments = [];

        for ($i = 0; $i < 3; $i++) {
            $expectedString .= 'A+B+1:2:3:4:5+D+E' . $unaSegment->segmentTerminator();
            $segments[] = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), 'A+B+1:2:3:4:5+D+E');
        }

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format(...$segments));
    }

    public function test_remove_loose_ends_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $givenString = '+A+B+1:2:::+DD++';
        $expectedString = '+A+B+1:2+DD' . $unaSegment->segmentTerminator();

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser(), $givenString);

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format($segment));
    }

    public function test_escaping_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $expectedString = '???:?+' . $unaSegment->segmentTerminator();

        $segment = AbstractSegmentTestSegment::fromAttributes('?:+');

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format($segment));
    }

    public function test_replace_space_character(): void
    {
        $unaSegment = new UnaSegment(':', '+', '.', '?', '_');
        $givenString = 'test replace space char';
        $expectedString = 'test_replace_space_char' . $unaSegment->segmentTerminator();

        $segment = AbstractSegmentTestSegment::fromAttributes($givenString);

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format($segment));
    }

    public function test_replace_decimal_point(): void
    {
        $unaSegment = new UnaSegment(':', '+', ',');
        $expectedString = 'A++1,23' . $unaSegment->segmentTerminator();

        $segment = AbstractSegmentTestSegment::fromAttributes('A', null, '1.23');

        static::assertSame($expectedString, new EdifactFormatter($unaSegment)->format($segment));
    }

    public function test_replacing_the_decimal_point_keeps_the_component_separator(): void
    {
        $unaSegment = new UnaSegment(':', '+', ',');
        $givenString = 'A++1,23:X';

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser($unaSegment), $givenString);

        static::assertSame(
            $givenString . $unaSegment->segmentTerminator(),
            new EdifactFormatter($unaSegment)->format($segment),
        );
    }

    public function test_una_prefix_is_written_only_once(): void
    {
        $formatter = new EdifactFormatter(UnaSegment::getDefault());
        $formatter->prefixUna();

        $segment = AbstractSegmentTestSegment::fromAttributes('A');

        static::assertSame("UNA:+.? 'A'", $formatter->format($segment));
        static::assertSame("A'", $formatter->format($segment));
    }

    public function test_formatting_no_segments_results_in_an_empty_string(): void
    {
        static::assertSame('', new EdifactFormatter(UnaSegment::getDefault())->format());
    }

    public function test_escaping_the_segment_terminator(): void
    {
        $segment = AbstractSegmentTestSegment::fromAttributes("a'b");

        static::assertSame("a?'b'", new EdifactFormatter(UnaSegment::getDefault())->format($segment));
    }
}
