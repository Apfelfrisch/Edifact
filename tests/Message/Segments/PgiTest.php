<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Pgi;
use Apfelfrisch\Edifact\Test\TestCase;

final class PgiTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pgi::fromAttributes('COD');

        $this->assertEquals('PGI', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString($delimiter), Pgi::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
