<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pgi;
use Apfelfrisch\Edifact\Test\TestCase;

final class PgiTest extends TestCase
{
    /** @test */
    public function test_pgi_segment(): void
    {
        $seg = Pgi::fromAttributes('COD');

        $this->assertEquals('PGI', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
