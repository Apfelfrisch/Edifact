<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Unt;
use Apfelfrisch\Edifact\Test\TestCase;

final class UntTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Unt::fromAttributes('213', 'REF');

        $this->assertEquals('UNT', $seg->name());
        $this->assertEquals('213', $seg->segCount());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
