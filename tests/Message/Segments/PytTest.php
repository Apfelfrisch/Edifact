<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Pyt;
use Proengeno\Edifact\Test\TestCase;

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
