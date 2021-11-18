<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pgi;
use Proengeno\Edifact\Test\TestCase;

final class PgiTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pgi::fromAttributes(new Delimiter(), 'COD');

        $this->assertEquals('PGI', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Pgi::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
