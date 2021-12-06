<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Com;
use Apfelfrisch\Edifact\Test\TestCase;

final class ComTest extends TestCase
{
    /** @test */
    public function test_com_segment(): void
    {
        $seg = Com::fromAttributes('ID', 'TYP');

        $this->assertEquals('COM', $seg->name());
        $this->assertEquals('ID', $seg->id());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
