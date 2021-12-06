<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Imd;
use Apfelfrisch\Edifact\Test\TestCase;

final class ImdTest extends TestCase
{
    /** @test */
    public function test_imd_segment(): void
    {
        $seg = Imd::fromAttributes('COD', 'QAL');

        $this->assertEquals('IMD', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
