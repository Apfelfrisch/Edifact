<?php

declare(strict_types = 1);

namespace Code\Php\Edifact\tests\Message\Segments;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Test\Fixtures\Segment;
use Apfelfrisch\Edifact\Validation\SegmentValidator;

class AbstractSegmentTest extends TestCase
{
    /** @test */
    public function it_provides_the_validator()
    {
        $segment = Segment::fromAttributes(new Delimiter(), 'A');

        $this->assertInstanceOf(SegmentValidator::class, $segment->getValidator());
    }

    /** @test */
    public function it_gives_its_segment_name()
    {
        $segment = Segment::fromSegLine(new Delimiter, 'A');

        $this->assertEquals('A', $segment->name());
    }

    /** @test */
    public function it_validates_itself()
    {
        $segment = Segment::fromSegLine(new Delimiter, "A++1:2::4:5");

        $this->assertNull($segment->validate());
    }

    /** @test */
    public function it_cast_itself_as_a_string()
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';

        $segment = Segment::fromSegLine(new Delimiter, $givenString);

        $this->assertEquals($givenString, $segment->toString(new Delimiter));
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

        $segment = Segment::fromSegLine(new Delimiter, $givenString);

        $this->assertEquals($expectedArray, $segment->toArray());
    }

    /** @test */
    public function it_removes_his_loose_ends_when_it_is_castet_to_a_string()
    {
        $givenString = "A+B+1:2:::+D++";
        $expectedString = "A+B+1:2+D";

        $segment = Segment::fromSegLine(new Delimiter, $givenString);

        $this->assertEquals($expectedString, $segment->toString(new Delimiter));
    }

    /** @test */
    public function it_handles_string_terminations()
    {
        $givenString = "A+B?+";

        $segment = Segment::fromSegLine(new Delimiter, $givenString);

        $this->assertEquals('B+', $segment->dummyMethod());
        $this->assertEquals($givenString, $segment->toString(new Delimiter));
    }
}
