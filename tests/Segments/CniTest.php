<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Cni;
use Apfelfrisch\Edifact\Test\TestCase;

final class CniTest extends TestCase
{
    /** @test */
    public function test_cni_segment(): void
    {
        $seg = Cni::fromAttributes('12345');

        $this->assertEquals('CNI', $seg->name());
        $this->assertEquals('12345', $seg->number());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
