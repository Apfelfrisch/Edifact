<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Cav;
use Apfelfrisch\Edifact\Test\TestCase;

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
