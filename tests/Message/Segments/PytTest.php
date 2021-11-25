<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Pyt;
use Apfelfrisch\Edifact\Test\TestCase;

final class PytTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pyt::fromAttributes('QUL');

        $this->assertEquals('PYT', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString($delimiter), Pyt::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
