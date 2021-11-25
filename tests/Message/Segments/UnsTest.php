<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Uns;
use Apfelfrisch\Edifact\Test\TestCase;

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
