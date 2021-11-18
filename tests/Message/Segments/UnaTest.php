<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Una;
use Proengeno\Edifact\Test\TestCase;

final class UnaTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Una::fromAttributes(':', '+', '.', '?', ' ');

        $this->assertEquals('UNA', $seg->name());
        $this->assertEquals(':', $seg->data());
        $this->assertEquals('+', $seg->dataGroup());
        $this->assertEquals('.', $seg->decimal());
        $this->assertEquals('?', $seg->terminator());
        $this->assertEquals(' ', $seg->emptyChar());

        $this->assertEquals($seg->toString($delimiter), Una::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
