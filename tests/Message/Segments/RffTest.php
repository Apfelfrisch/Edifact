<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Rff;
use Proengeno\Edifact\Test\TestCase;

final class RffTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Rff::fromAttributes(new Delimiter(), 'COD', 'ref-500-12');

        $this->assertEquals('RFF', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('ref-500-12', $seg->referenz());
        $this->assertEquals($seg->toString(), Rff::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
