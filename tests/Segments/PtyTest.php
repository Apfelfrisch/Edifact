<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pty;
use Apfelfrisch\Edifact\Test\TestCase;

final class PtyTest extends TestCase
{
    /** @test */
    public function test_pty_segment(): void
    {
        $seg = Pty::fromAttributes('QUL', 'PRIO-500');

        $this->assertEquals('PTY', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('PRIO-500', $seg->priority());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
