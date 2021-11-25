<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Agr;
use Apfelfrisch\Edifact\Test\TestCase;

final class AgrTest extends TestCase
{
    /** @test */
    public function test_agr_segment()
    {
        $delimiter = new Delimiter();
        $seg = Agr::fromAttributes('QAL', 'TYP');

        $this->assertEquals('AGR', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString($delimiter), Agr::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
