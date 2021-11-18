<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Eqd;
use Proengeno\Edifact\Test\TestCase;

final class EqdTest extends TestCase
{
    /** @test */
    public function test_segment()
    {
        $delimiter = new Delimiter();
        $seg = Eqd::fromAttributes('QAL', '12345');

        $this->assertEquals('EQD', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('12345', $seg->processNumber());
        $this->assertEquals($seg->toString($delimiter), Eqd::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
