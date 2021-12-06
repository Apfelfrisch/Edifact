<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\UnaSegment;
use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Pri;
use Apfelfrisch\Edifact\Test\TestCase;

final class PriTest extends TestCase
{
    /** @test */
    public function test_pri_segment(): void
    {
        $seg = Pri::fromAttributes('QUL', '20.00', 'EUR');

        $this->assertEquals('PRI', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals('EUR', $seg->unitCode());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }

    /** @test */
    public function test_setting_decimal_seperator(): void
    {
        $seg = Pri::fromSegLine(new SeglineParser(new UnaSegment(':', '+', '_')), 'QTY+QUL:20.00:EUR');

        $this->assertEquals('20.00', $seg->amount());
    }
}
