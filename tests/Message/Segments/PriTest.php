<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pri;
use Proengeno\Edifact\Test\TestCase;

final class PriTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pri::fromAttributes(new Delimiter(), 'QUL', '20.00', 'EUR');

        $this->assertEquals('PRI', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals('EUR', $seg->unitCode());
        $this->assertEquals($seg->toString(), Pri::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
