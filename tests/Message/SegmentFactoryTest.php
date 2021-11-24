<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Delimiter;
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
        $this->segFactory = SegmentFactory::withDefaultDegments();
    }

    /** @test */
    public function it_instanciates_the_segment_from_segment_string()
    {
        $this->assertInstanceOf($this->segmentNamespace . '\Bgm', $this->segFactory->build('BGM+', new Delimiter()));
    }

    /** @test **/
    public function it_instanciates_the_dafault_seg_if_its_allowed_and_no_secific_segement_was_found()
    {
        $this->segFactory = (new SegmentFactory)->addFallback(Fallback::class);
        $this->assertInstanceOf(Fallback::class, $this->segFactory->build('UKW', new Delimiter()));
    }

    /** @test **/
    public function it_throw_an_exception_if_no_default_seg_is_alowed_and_the_segment_is_unknowed()
    {
        $this->segFactory = new SegmentFactory($this->segmentNamespace);

        $this->expectException(EdifactException::class);
        $this->segFactory->build('UKW', new Delimiter());
    }
}
