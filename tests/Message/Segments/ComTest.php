<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Com;
use Proengeno\Edifact\Test\TestCase;

final class ComTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Com::fromAttributes(new Delimiter(), 'ID', 'TYP');

        $this->assertEquals('COM', $seg->name());
        $this->assertEquals('ID', $seg->id());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString(), Com::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
