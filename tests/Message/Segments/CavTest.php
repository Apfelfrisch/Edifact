<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Cav;
use Proengeno\Edifact\Test\TestCase;

final class CavTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Cav::fromAttributes('COD', 'RCD', 'VON', 'VTW', 'CLS');

        $this->assertEquals('CAV', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('RCD', $seg->responsCode());
        $this->assertEquals('VON', $seg->valueOne());
        $this->assertEquals('VTW', $seg->valueTwo());
        $this->assertEquals('CLS', $seg->codeList());
        $this->assertEquals($seg->toString($delimiter), Cav::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
