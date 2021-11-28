<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pyt;
use Apfelfrisch\Edifact\Test\TestCase;

final class PytTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pyt::fromAttributes('QUL');

        $this->assertEquals('PYT', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
