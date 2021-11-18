<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Pac;
use Proengeno\Edifact\Test\TestCase;

final class PacTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pac::fromAttributes('QAN', 'COD');

        $this->assertEquals('PAC', $seg->name());
        $this->assertEquals('QAN', $seg->quantity());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString($delimiter), Pac::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
