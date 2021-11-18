<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pyt;
use Proengeno\Edifact\Test\TestCase;

final class PytTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pyt::fromAttributes(new Delimiter(), 'QUL');

        $this->assertEquals('PYT', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals($seg->toString(), Pyt::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
