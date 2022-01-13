<?php

declare(strict_types = 1);

namespace Code\Php\Edifact\tests\Message\Segments;

use Apfelfrisch\Edifact\Segment\SeglineParser;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Test\Fixtures\AbstractSegmentTestSegment;

class AbstractSegmentTest extends TestCase
{
    /** @test */
    public function test_provide_segment_name(): void
    {
        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser, 'A');

        $this->assertEquals('A', $segment->name());
    }

    /** @test */
    public function test_string_casting(): void
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($givenString, $segment->toString());
    }

    /** @test */
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

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($expectedArray, $segment->toArray());
    }

    /** @test */
    public function test_remove_loose_ends(): void
    {
        $givenString = "A+B+1:2:::+D++";
        $expectedString = "A+B+1:2+D";

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($expectedString, $segment->toString());
    }

    /** @test */
    public function test_escaping_string(): void
    {
        $givenString = "A+?:?+";

        $segment = AbstractSegmentTestSegment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals(':+', $segment->dummyMethod());
        $this->assertEquals($givenString, $segment->toString());
    }

    /** @test */
    public function test_replace_space_character(): void
    {
        $givenString = "A+test_replace_space_char";

        $segment = AbstractSegmentTestSegment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', '.', '?', '_')),
            $givenString
        );

        $this->assertEquals('test replace space char', $segment->getValue('B', 'B'));
        $this->assertEquals('test replace space char', $segment->getValueFromPosition(1, 0));
    }

    /** @test */
    public function test_replace_decimal_point(): void
    {
        $givenString = "A++1,23";

        $segment = AbstractSegmentTestSegment::fromSegLine(
            new SeglineParser(new UnaSegment(':', '+', ',')),
            $givenString
        );

        $this->assertEquals('1.23', $segment->getValue('C', '1'));
        $this->assertEquals('1.23', $segment->getValueFromPosition(2, 0));
    }
}
