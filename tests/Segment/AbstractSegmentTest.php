<?php

declare(strict_types=1);

namespace Code\Php\Edifact\tests\Message\Segments;

use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Segment;
use Apfelfrisch\Edifact\Test\TestCase;

class AbstractSegmentTest extends TestCase
{
    public function test_provide_segment_name(): void
    {
        $segment = Segment::fromSegLine(new SeglineParser(), 'A');

        $this->assertEquals('A', $segment->name());
    }

    public function test_array_casting(): void
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedArray = [
            "A" => ["A" => "A",],
            "B" => ["B" => "B",],
            "C" => [1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5",],
            "D" => ["D" => "D",],
            "E" => ["E" => "E",],
        ];

        $segment = Segment::fromSegLine(new SeglineParser(), $givenString);

        $this->assertEquals($expectedArray, $segment->toArray());
    }

    public function test_escaping_string(): void
    {
        $givenString = "A+???:?+";

        $segment = Segment::fromSegLine(new SeglineParser(), $givenString);

        $this->assertEquals('?:+', $segment->getValue('B', 'B'));
        $this->assertEquals('?:+', $segment->getValueFromPosition(1, 0));
    }

    public function test_replace_space_character(): void
    {
        $givenString = "A+test_replace_space_char";

        $segment = Segment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', '.', '?', '_')),
            $givenString
        );

        $this->assertEquals('test replace space char', $segment->getValue('B', 'B'));
        $this->assertEquals('test replace space char', $segment->getValueFromPosition(1, 0));
    }

    public function test_replace_decimal_point(): void
    {
        $givenString = "A++1,23";

        $segment = Segment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', ',')),
            $givenString
        );

        $this->assertEquals('1.23', $segment->getValue('C', '1'));
        $this->assertEquals('1.23', $segment->getValueFromPosition(2, 0));
    }
}
