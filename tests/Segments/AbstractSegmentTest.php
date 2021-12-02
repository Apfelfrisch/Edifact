<?php

declare(strict_types = 1);

namespace Code\Php\Edifact\tests\Message\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Test\Fixtures\Segment;

class AbstractSegmentTest extends TestCase
{
    /** @test */
    public function it_gives_its_segment_name()
    {
        $segment = Segment::fromSegLine(new SeglineParser, 'A');

        $this->assertEquals('A', $segment->name());
    }

    /** @test */
    public function it_cast_itself_as_a_string()
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';

        $segment = Segment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($givenString, $segment->toString(new SeglineParser));
    }

    /** @test */
    public function it_cast_itself_as_an_array()
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedArray = [
            "A" => ["A" => "A",],
            "B" => ["B" => "B",],
            "C" => [1 => "1", 2 => "2", 3 => "3", 4 => "4", 5 => "5",],
            "D" => ["D" => "D",],
            "E" => ["E" => "E",],
            "F" => ["F" => null,],
        ];

        $segment = Segment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($expectedArray, $segment->toArray());
    }

    /** @test */
    public function it_removes_his_loose_ends_when_it_is_castet_to_a_string()
    {
        $givenString = "A+B+1:2:::+D++";
        $expectedString = "A+B+1:2+D";

        $segment = Segment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals($expectedString, $segment->toString());
    }

    /** @test */
    public function it_handles_string_terminations()
    {
        $givenString = "A+B?+";

        $segment = Segment::fromSegLine(new SeglineParser, $givenString);

        $this->assertEquals('B+', $segment->dummyMethod());
        $this->assertEquals($givenString, $segment->toString());
    }
}
