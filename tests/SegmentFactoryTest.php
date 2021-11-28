<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Test\TestCase;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Exceptions\EdifactException;

class SegmentFactoryTest extends TestCase
{
    private $segFactory;
    private $segmentNamespace = '\Apfelfrisch\Edifact\Segments';

    public function setUp(): void
    {
        $this->segFactory = SegmentFactory::withDefaultDegments();
    }

    /** @test */
    public function it_instanciates_the_segment_from_segment_string()
    {
        $this->assertInstanceOf($this->segmentNamespace . '\Bgm', $this->segFactory->build('BGM+'));
    }

    /** @test **/
    public function it_instanciates_the_dafault_seg_if_its_allowed_and_no_secific_segement_was_found()
    {
        $this->segFactory = (new SegmentFactory)->addFallback(Generic::class);
        $this->assertInstanceOf(Generic::class, $this->segFactory->build('UKW'));
    }

    /** @test **/
    public function it_throw_an_exception_if_no_default_seg_is_alowed_and_the_segment_is_unknowed()
    {
        $this->segFactory = new SegmentFactory();

        $this->expectException(EdifactException::class);
        $this->segFactory->build('UKW');
    }
}
