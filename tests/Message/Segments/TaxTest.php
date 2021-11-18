<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Tax;
use Proengeno\Edifact\Test\TestCase;

final class TaxTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Tax::fromAttributes(new Delimiter(), 'QUL', 'TYP', 'RATE-ONE', 'CAT');

        $this->assertEquals('TAX', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals('RATE-ONE', $seg->rate());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals($seg->toString(), Tax::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
