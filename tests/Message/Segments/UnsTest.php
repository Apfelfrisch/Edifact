<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Uns;
use Proengeno\Edifact\Test\TestCase;

final class UnsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Uns::fromAttributes(new Delimiter(), 'S');

        $this->assertEquals('UNS', $seg->name());
        $this->assertEquals('S', $seg->code());

        $this->assertEquals($seg->toString(), Uns::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
