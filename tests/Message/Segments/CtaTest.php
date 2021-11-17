<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cta;
use Proengeno\Edifact\Test\TestCase;

final class CtaTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cta::fromAttributes(new Delimiter(), 'TYP', 'NAME');

        $this->assertEquals('CTA', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('NAME', $seg->employee());
        $this->assertEquals($seg->toString(), Cta::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
