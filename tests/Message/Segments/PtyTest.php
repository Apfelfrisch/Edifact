<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pty;
use Proengeno\Edifact\Test\TestCase;

final class PtyTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pty::fromAttributes(new Delimiter(), 'QUL', 'PRIO-500');

        $this->assertEquals('PTY', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('PRIO-500', $seg->priority());
        $this->assertEquals($seg->toString(), Pty::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
