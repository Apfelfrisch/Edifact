<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cux;
use Proengeno\Edifact\Test\TestCase;

final class CuxTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cux::fromAttributes(new Delimiter(), 'TYP', 'EUR', 'QUL');

        $this->assertEquals('CUX', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('EUR', $seg->currency());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString(), Cux::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
