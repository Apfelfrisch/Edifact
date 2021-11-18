<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Seq;
use Proengeno\Edifact\Test\TestCase;

final class SeqTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Seq::fromAttributes(new Delimiter(), 'COD');

        $this->assertEquals('SEQ', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Seq::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
