<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\SegmentFactory;
use Proengeno\Edifact\Segments\Fallback;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\SegValidationException;

class SegmentFactoryTest extends TestCase
{
    private $segFactory;
    private $segmentNamespace = '\Proengeno\Edifact\Segments';

    public function setUp(): void
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

    /** @test **/
    public function it_instanciates_the_dafault_seg_if_its_allowed_and_no_secific_segement_was_found()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace, null, Fallback::class);
        $this->assertInstanceOf(Fallback::class, $this->segFactory->fromSegline('UKW'));
    }

    /** @test **/
    public function it_throw_an_exception_if_no_default_seg_is_alowed_and_the_segment_is_unknowed()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace);

        $this->expectException(EdifactException::class);
        $this->segFactory->fromSegline('UKW');
    }

    /** @test **/
    public function it_throw_an_exception_if_we_try_to_instanciates_the_default_seg_from_attributes()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace);

        $this->expectException(SegValidationException::class);
        $this->segFactory->fromAttributes('UKW');
    }
}
