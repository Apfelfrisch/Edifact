<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\SegmentFactory;
use Proengeno\Edifact\Message\GenericSegment;

class SegmentFactoryTest extends TestCase
{
    private $segFactory;
    private $segmentNamespace = '\Proengeno\Edifact\Test\Fixtures\Segments';

    public function setUp()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace);
    }

    /** @test */
    public function it_instanciates_the_segment_from_segment_string()
    {
        $this->assertInstanceOf($this->segmentNamespace . '\Bgm', $this->segFactory->fromSegline('BGM+'));
    }

    /** @test */
    public function it_instanciates_the_segment_from_attributes()
    {
        $this->assertInstanceOf($this->segmentNamespace . '\Bgm', $this->segFactory->fromAttributes('BGM', ['380', '12345']));
    }

    /**
     * @test
     **/
    public function it_instanciates_the_dafault_seg_if_its_allowed_and_no_secific_segement_was_found()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace, null, GenericSegment::class);
        $this->assertInstanceOf(GenericSegment::class, $this->segFactory->fromSegline('UKW'));
    }

    /**
     * @test
     * @expectedException Proengeno\Edifact\Exceptions\EdifactException
     **/
    public function it_throw_an_exception_if_no_default_seg_is_alowed_and_the_segment_is_unknowed()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace);
        $this->assertInstanceOf(GenericSegment::class, $this->segFactory->fromSegline('UKW'));
    }

    /**
     * @test
     * @expectedException Proengeno\Edifact\Exceptions\EdifactException
     **/
    public function it_throw_an_exception_if_we_try_to_instanciates_the_default_seg_from_attributes()
    {
        $this->segFactory->fromAttributes('UKW');
    }
}
