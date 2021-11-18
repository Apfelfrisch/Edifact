<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Seq;
use Proengeno\Edifact\Test\TestCase;

final class SeqTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Seq::fromAttributes('COD');

        $this->assertEquals('SEQ', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString($delimiter), Seq::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
