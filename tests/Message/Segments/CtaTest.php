<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Cta;
use Apfelfrisch\Edifact\Test\TestCase;

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
