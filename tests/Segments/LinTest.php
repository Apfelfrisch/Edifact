<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Lin;
use Apfelfrisch\Edifact\Test\TestCase;

final class LinTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Lin::fromAttributes('123456', 'ARTICLE_NO_1', 'COD');

        $this->assertEquals('LIN', $seg->name());
        $this->assertEquals('123456', $seg->number());
        $this->assertEquals('ARTICLE_NO_1', $seg->articleNumber());
        $this->assertEquals('COD', $seg->articleCode());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
