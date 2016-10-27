<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\SegmentFactory;

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
}
