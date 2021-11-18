<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unt;
use Proengeno\Edifact\Test\TestCase;

final class UntTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Unt::fromAttributes(new Delimiter(), '213', 'REF');

        $this->assertEquals('UNT', $seg->name());
        $this->assertEquals('213', $seg->segCount());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString(), Unt::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
