<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Pty;
use Proengeno\Edifact\Test\TestCase;

final class PtyTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pty::fromAttributes('QUL', 'PRIO-500');

        $this->assertEquals('PTY', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('PRIO-500', $seg->priority());
        $this->assertEquals($seg->toString($delimiter), Pty::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
