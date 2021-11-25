<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Seq;
use Apfelfrisch\Edifact\Test\TestCase;

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
