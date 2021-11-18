<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Imd;
use Proengeno\Edifact\Test\TestCase;

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
