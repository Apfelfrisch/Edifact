<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Unz;
use Apfelfrisch\Edifact\Test\TestCase;

final class UnzTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Unz::fromAttributes('213', 'REF');

        $this->assertEquals('UNZ', $seg->name());
        $this->assertEquals('213', $seg->counter());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
