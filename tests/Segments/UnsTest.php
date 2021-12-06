<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Uns;
use Apfelfrisch\Edifact\Test\TestCase;

final class UnsTest extends TestCase
{
    /** @test */
    public function test_uns_segment(): void
    {
        $seg = Uns::fromAttributes('S');

        $this->assertEquals('UNS', $seg->name());
        $this->assertEquals('S', $seg->code());

        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
