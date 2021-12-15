<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments\GenericSegment;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
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

        $this->expectException(EdifactException::class);
        $this->segFactory->build('UKW');
    }
}
