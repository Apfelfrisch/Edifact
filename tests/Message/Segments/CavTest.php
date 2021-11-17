<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cav;
use Proengeno\Edifact\Test\TestCase;

final class CavTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cav::fromAttributes(new Delimiter(), 'COD', 'RCD', 'VON', 'VTW', 'CLS');

        $this->assertEquals('CAV', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('RCD', $seg->responsCode());
        $this->assertEquals('VON', $seg->valueOne());
        $this->assertEquals('VTW', $seg->valueTwo());
        $this->assertEquals('CLS', $seg->codeList());
        $this->assertEquals($seg->toString(), Cav::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
