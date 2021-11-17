<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Agr;
use Proengeno\Edifact\Test\TestCase;

final class AgrTest extends TestCase
{
    /** @test */
    public function test_agr_segment()
    {
        $seg = Agr::fromAttributes(new Delimiter(), 'QAL', 'TYP');

        $this->assertEquals('AGR', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString(), Agr::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
