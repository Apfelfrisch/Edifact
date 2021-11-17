<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ajt;
use Proengeno\Edifact\Test\TestCase;

final class AjtTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Ajt::fromAttributes(new Delimiter(), 'COD');

        $this->assertEquals('AJT', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Ajt::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
