<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Doc;
use Proengeno\Edifact\Test\TestCase;

final class DocTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Doc::fromAttributes(new Delimiter(), 'COD', 'NUMBER_1');

        $this->assertEquals('DOC', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('NUMBER_1', $seg->number());
        $this->assertEquals($seg->toString(), Doc::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
