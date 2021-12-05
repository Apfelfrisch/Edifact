<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Ajt;
use Apfelfrisch\Edifact\Test\TestCase;

final class AjtTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Ajt::fromAttributes('COD');

        $this->assertEquals('AJT', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Ajt::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
