<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Cta;
use Proengeno\Edifact\Test\TestCase;

final class CtaTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Cta::fromAttributes('TYP', 'NAME');

        $this->assertEquals('CTA', $seg->name());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('NAME', $seg->employee());
        $this->assertEquals($seg->toString($delimiter), Cta::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
