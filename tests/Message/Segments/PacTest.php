<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pac;
use Proengeno\Edifact\Test\TestCase;

final class PacTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pac::fromAttributes(new Delimiter(), 'QAN', 'COD');

        $this->assertEquals('PAC', $seg->name());
        $this->assertEquals('QAN', $seg->quantity());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Pac::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
