<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Sts;
use Apfelfrisch\Edifact\Test\TestCase;

final class StsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Sts::fromAttributes('CAT', 'RES', 'COD', 'STA');

        $this->assertEquals('STS', $seg->name());
        $this->assertEquals('CAT', $seg->category());
        $this->assertEquals('RES', $seg->reason());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('STA', $seg->status());
        $this->assertEquals($seg->toString($delimiter), Sts::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
