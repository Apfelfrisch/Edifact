<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Moa;
use Apfelfrisch\Edifact\Test\TestCase;

final class MoaTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Moa::fromAttributes('QUL', 20.00);

        $this->assertEquals('MOA', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
        $this->assertEquals($seg->toString($delimiter), Moa::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }

    /** @test */
    public function test_setting_decimal_seperator(): void
    {
        $seg = Moa::fromSegLine(new Delimiter(':', '+', '_'), 'MOA+QUL:20_00');

        $this->assertEquals('MOA', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('20.00', $seg->amount());
    }
}
