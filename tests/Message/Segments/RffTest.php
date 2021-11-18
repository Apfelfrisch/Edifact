<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Rff;
use Proengeno\Edifact\Test\TestCase;

final class RffTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Rff::fromAttributes('COD', 'ref-500-12');

        $this->assertEquals('RFF', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('ref-500-12', $seg->referenz());
        $this->assertEquals($seg->toString($delimiter), Rff::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
