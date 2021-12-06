<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Exceptions\EdifactException;

class SegmentFactoryTest extends TestCase
{
    private SegmentFactory $segFactory;
    private string $segmentNamespace = '\Apfelfrisch\Edifact\Segments';

    public function setUp(): void
    {
        $this->segFactory = SegmentFactory::withDefaultDegments();
    }

    /** @test */
    public function test_instanciate_the_segment_from_segment_string(): void
    {
        /** @psalm-var class-string */
        $segmentClass = $this->segmentNamespace . '\Bgm';
        $this->assertInstanceOf($segmentClass, $this->segFactory->build('BGM+'));
    }

    /** @test **/
    public function test_instanciate_the_dafault_seg_if_its_allowed_and_no_specific_segement_was_found(): void
    {
        $this->segFactory = (new SegmentFactory)->addFallback(Generic::class);
        $this->assertInstanceOf(Generic::class, $this->segFactory->build('UKW'));
    }

    /** @test **/
    public function test_throw_an_exception_if_no_default_seg_is_allowed_and_the_segment_is_unknowed(): void
    {
        $this->segFactory = new SegmentFactory();

        $this->expectException(EdifactException::class);
        $this->segFactory->build('UKW');
    }
}
