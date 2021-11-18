<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Cux;
use Proengeno\Edifact\Test\TestCase;

final class CuxTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Cux::fromAttributes('TYP', 'EUR', 'QUL');

        $this->assertEquals('CUX', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('EUR', $seg->currency());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString($delimiter), Cux::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
