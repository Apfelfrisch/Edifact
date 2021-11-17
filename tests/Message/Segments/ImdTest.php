<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Imd;
use Proengeno\Edifact\Test\TestCase;

final class ImdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Imd::fromAttributes(new Delimiter(), 'COD', 'QAL');

        $this->assertEquals('IMD', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals($seg->toString(), Imd::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
