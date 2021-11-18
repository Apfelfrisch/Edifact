<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Uns;
use Proengeno\Edifact\Test\TestCase;

final class UnsTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Uns::fromAttributes('S');

        $this->assertEquals('UNS', $seg->name());
        $this->assertEquals('S', $seg->code());

        $this->assertEquals($seg->toString($delimiter), Uns::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
