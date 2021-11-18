<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Pri;
use Proengeno\Edifact\Test\TestCase;

final class PriTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pri::fromAttributes('QUL', '20.00', 'EUR');

        $this->assertEquals('PRI', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals('EUR', $seg->unitCode());
        $this->assertEquals($seg->toString($delimiter), Pri::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }

    /** @test */
    public function test_setting_decimal_seperator(): void
    {
        $seg = Pri::fromSegLine(new Delimiter(':', '+', '_'), 'QTY+QUL:20.00:EUR');

        $this->assertEquals('20.00', $seg->amount());
    }
}
