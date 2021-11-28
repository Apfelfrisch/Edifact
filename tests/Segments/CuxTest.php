<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Cux;
use Apfelfrisch\Edifact\Test\TestCase;

final class CuxTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cux::fromAttributes('TYP', 'EUR', 'QUL');

        $this->assertEquals('CUX', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('EUR', $seg->currency());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
