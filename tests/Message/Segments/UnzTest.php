<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Unz;
use Proengeno\Edifact\Test\TestCase;

final class UnzTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Unz::fromAttributes('213', 'REF');

        $this->assertEquals('UNZ', $seg->name());
        $this->assertEquals('213', $seg->counter());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString($delimiter), Unz::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
