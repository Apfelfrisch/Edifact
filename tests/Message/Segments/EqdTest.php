<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Eqd;
use Proengeno\Edifact\Test\TestCase;

final class EqdTest extends TestCase
{
    /** @test */
    public function test_segment()
    {
        $seg = Eqd::fromAttributes(new Delimiter(), 'QAL', '12345');

        $this->assertEquals('EQD', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('12345', $seg->processNumber());
        $this->assertEquals($seg->toString(), Eqd::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
