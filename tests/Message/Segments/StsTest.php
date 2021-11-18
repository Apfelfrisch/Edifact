<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Sts;
use Proengeno\Edifact\Test\TestCase;

final class StsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Sts::fromAttributes(new Delimiter(), 'CAT', 'RES', 'COD', 'STA');

        $this->assertEquals('STS', $seg->name());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals('RES', $seg->reason());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('STA', $seg->status());
        $this->assertEquals($seg->toString(), Sts::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
