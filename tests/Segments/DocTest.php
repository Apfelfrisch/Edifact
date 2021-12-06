<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Doc;
use Apfelfrisch\Edifact\Test\TestCase;

final class DocTest extends TestCase
{
    /** @test */
    public function test_doc_segment(): void
    {
        $seg = Doc::fromAttributes('COD', 'NUMBER_1');

        $this->assertEquals('DOC', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('NUMBER_1', $seg->number());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }
}
