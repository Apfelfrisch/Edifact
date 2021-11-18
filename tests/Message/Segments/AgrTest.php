<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Agr;
use Proengeno\Edifact\Test\TestCase;

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
