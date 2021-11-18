<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unz;
use Proengeno\Edifact\Test\TestCase;

final class UnzTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Unz::fromAttributes(new Delimiter(), '213', 'REF');

        $this->assertEquals('UNZ', $seg->name());
        $this->assertEquals('213', $seg->counter());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString(), Unz::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
