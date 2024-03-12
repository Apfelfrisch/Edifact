<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Formatte;

use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Segment;
use Apfelfrisch\Edifact\Test\TestCase;

final class EdifactFormatterTest extends TestCase
{
    public function test_formatting_one_segment_to_edifact_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $givenString = "A+B+1:2:3:4:5+D+E";
        $expectedString = $givenString . $unaSegment->segmentTerminator();

        $segment = Segment::fromSegLine(new SeglineParser(), $givenString);

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format($segment));
    }

    public function test_formatting_multiple_segments_to_edifact_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $expectedString = "";

        $segments = [];
        for ($i = 0; $i < 3; $i++) {
            $expectedString .= "A+B+1:2:3:4:5+D+E" . $unaSegment->segmentTerminator();
            $segments[] = Segment::fromSegLine(new SeglineParser(), "A+B+1:2:3:4:5+D+E");
        }

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format(...$segments));
    }

    /** @test */
    public function test_remove_loose_ends_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $givenString = "+A+B+1:2:::+DD++";
        $expectedString = "+A+B+1:2+DD" . $unaSegment->segmentTerminator();

        $segment = Segment::fromSegLine(new SeglineParser(), $givenString);

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format($segment));
    }

    /** @test */
    public function test_escaping_string(): void
    {
        $unaSegment = UnaSegment::getDefault();
        $expectedString = "???:?+?'" . $unaSegment->segmentTerminator();

        $segment = Segment::fromAttributes("?:+'");

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format($segment));
    }

    public function test_replace_space_character(): void
    {
        $unaSegment = new UnaSegment(':', '+', '.', '?', '_');
        $givenString = "test replace space char";
        $expectedString = "test_replace_space_char" . $unaSegment->segmentTerminator();
        ;

        $segment = Segment::fromAttributes($givenString);

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format($segment));
    }

    /** @test */
    public function test_replace_decimal_point(): void
    {
        $unaSegment = new UnaSegment(':', '+', ',');
        $expectedString = "A++1,23" . $unaSegment->segmentTerminator();

        $segment = Segment::fromAttributes('A', null, '1.23');

        $this->assertSame($expectedString, (new EdifactFormatter($unaSegment))->format($segment));
    }
}
