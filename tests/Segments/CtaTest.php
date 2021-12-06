<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Cta;
use Apfelfrisch\Edifact\Test\TestCase;

final class CtaTest extends TestCase
{
    /** @test */
    public function test_cta_segment(): void
    {
        $seg = Cta::fromAttributes('TYP', 'NAME');

        $this->assertEquals('CTA', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('NAME', $seg->employee());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
