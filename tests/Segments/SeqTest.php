<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Seq;
use Apfelfrisch\Edifact\Test\TestCase;

final class SeqTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Seq::fromAttributes('COD');

        $this->assertEquals('SEQ', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
