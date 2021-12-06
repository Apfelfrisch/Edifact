<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Test\TestCase;

final class GenericTest extends TestCase
{
    /** @test */
    public function test_generic_segment(): void
    {
        $seg = Generic::fromAttributes('TST', ['a', '1', '2'], ['b', '1']);

        $this->assertEquals('TST', $seg->name());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
