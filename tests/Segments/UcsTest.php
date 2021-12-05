<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Ucs;
use Apfelfrisch\Edifact\Test\TestCase;

final class UcsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Ucs::fromAttributes('500', '12');

        $this->assertEquals('UCS', $seg->name());
        $this->assertEquals('500', $seg->position());
        $this->assertEquals('12', $seg->error());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
