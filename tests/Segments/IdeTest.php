<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Ide;
use Apfelfrisch\Edifact\Test\TestCase;

final class IdeTest extends TestCase
{
    /** @test */
    public function test_ide_segment(): void
    {
        $seg = Ide::fromAttributes('QAL', 'ID50');

        $this->assertEquals('IDE', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('ID50', $seg->idNumber());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
