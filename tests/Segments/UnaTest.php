<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Una;
use Apfelfrisch\Edifact\Test\TestCase;

final class UnaTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Una::fromAttributes(':', '+', '.', '?', ' ');

        $this->assertEquals('UNA', $seg->name());
        $this->assertEquals(':', $seg->componentSeparator());
        $this->assertEquals('+', $seg->elementSeparator());
        $this->assertEquals('.', $seg->decimalPoint());
        $this->assertEquals('?', $seg->escapeCharacter());
        $this->assertEquals(' ', $seg->spaceCharacter());

        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
