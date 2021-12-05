<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Eqd;
use Apfelfrisch\Edifact\Test\TestCase;

final class EqdTest extends TestCase
{
    /** @test */
    public function test_segment()
    {
        $seg = Eqd::fromAttributes('QAL', '12345');

        $this->assertEquals('EQD', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('12345', $seg->processNumber());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
