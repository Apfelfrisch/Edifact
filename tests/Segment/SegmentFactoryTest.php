<?php

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Unh;
use Apfelfrisch\Edifact\Test\TestCase;

class SegmentFactoryTest extends TestCase
{
    /** @test */
    public function test_instanciate_the_segment_from_segment_string(): void
    {
        /** @var class-string */
        $segmentClass = Unh::class;

        $segFactory = (new ConcreteSegmentFactory)->addSegment('UNH', Unh::class);

        $this->assertSame(Unh::class, $segFactory->getClassname('UNH'));
        $this->assertInstanceOf($segmentClass, $segFactory->build('UNH+'));
    }

    /** @test */
    public function test_using_always_upper_case_as_segment_key(): void
    {
        /** @var class-string */
        $segmentClass = Unh::class;

        $segFactory = (new ConcreteSegmentFactory)->addSegment('unh', Unh::class);

        $this->assertSame(Unh::class, $segFactory->getClassname('UNH'));
        $this->assertInstanceOf($segmentClass, $segFactory->build('UNH+'));
    }

    /** @test **/
    public function test_instanciate_the_dafault_seg_if_its_allowed_and_no_specific_segement_was_found(): void
    {
        $segFactory = (new ConcreteSegmentFactory)->addFallback(GenericSegment::class);

        $this->assertInstanceOf(GenericSegment::class, $segFactory->build('UKW'));
    }

    /** @test **/
    public function test_throw_an_exception_if_no_default_seg_is_allowed_and_the_segment_is_unknowed(): void
    {
        $segFactory = new ConcreteSegmentFactory();

        $this->expectException(InvalidEdifactContentException::class);
        $segFactory->build('UKW');
    }
}
