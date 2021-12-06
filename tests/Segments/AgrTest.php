<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Agr;
use Apfelfrisch\Edifact\Test\TestCase;

final class AgrTest extends TestCase
{
    /** @test */
    public function test_agr_segment(): void
    {
        $seg = Agr::fromAttributes('QAL', 'TYP');

        $this->assertEquals('AGR', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString(), Agr::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
