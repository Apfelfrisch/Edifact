<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ide;
use Proengeno\Edifact\Test\TestCase;

final class IdeTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Ide::fromAttributes(new Delimiter(), 'QAL', 'ID50');

        $this->assertEquals('IDE', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('ID50', $seg->idNumber());
        $this->assertEquals($seg->toString(), Ide::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
