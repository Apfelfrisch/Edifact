<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Qty;
use Proengeno\Edifact\Test\TestCase;

final class QtyTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Qty::fromAttributes(new Delimiter(), 'QUL', '20.00', 'EUR');

        $this->assertEquals('QTY', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals('EUR', $seg->unitCode());
        $this->assertEquals($seg->toString(), Qty::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
