<?php

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Unh;

class SegmentFactoryTest extends TestCase
{
    private SegmentFactory $segFactory;

    public function setUp(): void
    {
        $this->segFactory = SegmentFactory::fromDefault();
    }

    /** @test */
    public function test_instanciate_the_segment_from_segment_string(): void
    {
        /** @psalm-var class-string */
        $segmentClass = Unh::class;
        $this->assertInstanceOf($segmentClass, $this->segFactory->build('UNH+'));
    }

    /** @test **/
    public function test_instanciate_the_dafault_seg_if_its_allowed_and_no_specific_segement_was_found(): void
    {
        $this->segFactory = (new SegmentFactory)->addFallback(GenericSegment::class);
        $this->assertInstanceOf(GenericSegment::class, $this->segFactory->build('UKW'));
    }

    /** @test **/
    public function test_throw_an_exception_if_no_default_seg_is_allowed_and_the_segment_is_unknowed(): void
    {
        $this->segFactory = new SegmentFactory();

        $this->expectException(InvalidEdifactContentException::class);
        $this->segFactory->build('UKW');
    }
}
