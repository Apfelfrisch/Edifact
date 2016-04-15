<?php

use Mockery as m;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unh;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

class SegFrameworkTest extends TestCase 
{
    protected function setUp()
    {
        SegmentDummy::setDelimiter(null);
        SegmentDummy::setValidator(null);
    }

    /** @test */
    public function it_can_set_a_costum_delimter()
    {
        $customDelimiter = new Delimiter;
        $segmentDummy = SegmentDummy::fromAttributes('A');

        $segmentDummy->setDelimiter($customDelimiter);

        $this->assertEquals($customDelimiter, $segmentDummy->getDelimiter());
    }

    /** @test */
    public function it_gives_a_standard_delimiter_if_none_was_set()
    {
        $segmentDummy = SegmentDummy::fromAttributes('A');

        $this->assertInstanceOf(Delimiter::class, $segmentDummy->getDelimiter());
    }

    /** @test */
    public function it_can_set_a_costum_validator()
    {
        $customValidator = new SegmentValidator;
        $segmentDummy = SegmentDummy::fromAttributes('A');

        SegmentDummy::setValidator($customValidator);

        $this->assertEquals($customValidator, $segmentDummy->getValidator());
    }

    /** @test */
    public function it_gives_a_standard_validator_if_none_was_set()
    {
        $segmentDummy = SegmentDummy::fromAttributes('A');

        $this->assertInstanceOf(SegmentValidator::class, $segmentDummy->getValidator());
    }

    /** @test */
    public function it_gives_its_segment_name()
    {
        $segmentDummy = SegmentDummy::fromSegLine('A');

        $this->assertEquals('A', $segmentDummy->name());
    }

    /** @test */
    public function it_validates_itself()
    {
        $customValidator = m::mock(SegValidatorInterface::class, function($customValidator) {
            $customValidator->shouldReceive('validate')->once();
        });
        $segmentDummy = SegmentDummy::fromSegLine('A');

        SegmentDummy::setValidator($customValidator);

        $segmentDummy->validate();
    }

    /** @test */
    public function it_can_cast_to_a_string()
    {
        $givenString = 'A+B+1:2:3:4:5+D+E';
        $expectedString = $givenString;

        $segmentDummy = SegmentDummy::fromSegLine($givenString);

        $this->assertEquals($expectedString, (string)$segmentDummy);
    }

    /** @test */
    public function it_removes_his_loose_ends_when_it_is_castet_to_a_string()
    {
        $givenString = 'A+B+1:2:::+D++';
        $expectedString = 'A+B+1:2+D';

        $segmentDummy = SegmentDummy::fromSegLine($givenString);

        $this->assertEquals($expectedString, (string)$segmentDummy);
    }

}
