<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\UnaSegment;
use Apfelfrisch\Edifact\SeglineParser;
use Apfelfrisch\Edifact\Segments\Qty;
use Apfelfrisch\Edifact\Test\TestCase;

final class QtyTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Qty::fromAttributes('QUL', '20.00', 'EUR');

        $this->assertEquals('QTY', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals('EUR', $seg->unitCode());
        $this->assertEquals($seg->toString(), $seg::fromSegLine(new SeglineParser, $seg->toString())->toString());
    }

    /** @test */
    public function test_setting_decimal_seperator(): void
    {
        $seg = Qty::fromSegLine(new SeglineParser(new UnaSegment(':', '+', '_')), 'QTY+QUL:20.00:EUR');

        $this->assertEquals('20.00', $seg->amount());
    }
}
