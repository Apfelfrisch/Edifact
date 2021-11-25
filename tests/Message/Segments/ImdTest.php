<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Imd;
use Apfelfrisch\Edifact\Test\TestCase;

final class ImdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Imd::fromAttributes('COD', 'QAL');

        $this->assertEquals('IMD', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals($seg->toString($delimiter), Imd::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
