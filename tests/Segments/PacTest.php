<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pac;
use Apfelfrisch\Edifact\Test\TestCase;

final class PacTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pac::fromAttributes('QAN', 'COD');

        $this->assertEquals('PAC', $seg->name());
        $this->assertEquals('QAN', $seg->quantity());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
