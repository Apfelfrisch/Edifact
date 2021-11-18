<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ucs;
use Proengeno\Edifact\Test\TestCase;

final class UcsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Ucs::fromAttributes(new Delimiter(), '500', '12');

        $this->assertEquals('UCS', $seg->name());
        $this->assertEquals('500', $seg->position());
        $this->assertEquals('12', $seg->error());
        $this->assertEquals($seg->toString(), Ucs::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
