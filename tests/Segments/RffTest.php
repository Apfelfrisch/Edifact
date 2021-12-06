<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Rff;
use Apfelfrisch\Edifact\Test\TestCase;

final class RffTest extends TestCase
{
    /** @test */
    public function test_rff_segment(): void
    {
        $seg = Rff::fromAttributes('COD', 'ref-500-12');

        $this->assertEquals('RFF', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('ref-500-12', $seg->referenz());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
