<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Cav;
use Apfelfrisch\Edifact\Test\TestCase;

final class CavTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cav::fromAttributes('COD', 'RCD', 'VON', 'VTW', 'CLS');

        $this->assertEquals('CAV', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('RCD', $seg->responsCode());
        $this->assertEquals('VON', $seg->valueOne());
        $this->assertEquals('VTW', $seg->valueTwo());
        $this->assertEquals('CLS', $seg->codeList());
        $this->assertEquals($seg->toString(), Cav::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
