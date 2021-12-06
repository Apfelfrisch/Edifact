<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Bgm;
use Apfelfrisch\Edifact\Test\TestCase;

final class BgmTest extends TestCase
{
    /** @test */
    public function test_bgm_segment(): void
    {
        $seg = Bgm::fromAttributes('DCO', 'DNO', 'MCO');

        $this->assertEquals('BGM', $seg->name());
        $this->assertEquals('DCO', $seg->docCode());
        $this->assertEquals('DNO', $seg->docNumber());
        $this->assertEquals('MCO', $seg->messageCode());
        $this->assertEquals($seg->toString(), Bgm::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
