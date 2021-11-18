<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Unt;
use Proengeno\Edifact\Test\TestCase;

final class UntTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Unt::fromAttributes('213', 'REF');

        $this->assertEquals('UNT', $seg->name());
        $this->assertEquals('213', $seg->segCount());
        $this->assertEquals('REF', $seg->referenz());

        $this->assertEquals($seg->toString($delimiter), Unt::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
