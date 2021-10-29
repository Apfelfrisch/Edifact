<?php

namespace Proengeno\Edifact\Test\Templates;

use Mockery as m;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Test\Fixtures\Segment;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

class AbstractSegmentTest extends TestCase
{
    /** @test */
    public function it_gives_its_segment_name()
    {
        $segment = Segment::fromAttributes('A');

        $this->assertEquals('A', $segment->name());
    }

    /** @test */
    public function it_validates_itself()
    {
        $customValidator = m::mock(SegValidatorInterface::class, function($customValidator) {
            $customValidator->shouldReceive('validate')->once();
        });

        $segment = Segment::fromAttributes('A');

        $this->assertInstanceOf(get_class($segment), $segment->validate($customValidator));
    }

    /** @test */
    public function it_can_cast_to_a_string()
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedString = $givenString . "'";

        $segment = Segment::fromSegline($givenString);

        $this->assertEquals($expectedString, $segment->toString());
    }

    /** @test */
    public function it_removes_his_loose_ends_when_it_is_castet_to_a_string()
    {
        $givenString = 'A+B+1:2:::+D++';
        $expectedString = "A+B+1:2+D'";

        $segment = Segment::fromSegline($givenString);

        $this->assertEquals($expectedString, $segment->toString());
    }

    /** @test */
    public function it_can_grap_a_value_over_an_element_key()
    {
        $segment = Segment::fromSegline("Aa+Bb");

        $this->assertEquals('Aa', $segment->getA());
        $this->assertEquals('Bb', $segment->getB());
    }
}
