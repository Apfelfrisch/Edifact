<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pcd;
use Apfelfrisch\Edifact\Test\TestCase;

final class PcdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pcd::fromAttributes('30', '5');

        $this->assertEquals('PCD', $seg->name());
        $this->assertEquals('30', $seg->percent());
        $this->assertEquals('5', $seg->qualifier());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
