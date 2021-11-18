<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Loc;
use Proengeno\Edifact\Test\TestCase;

final class LocTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Loc::fromAttributes('QUL', 'NO_1', '1');

        $this->assertEquals('LOC', $seg->name());
        $this->assertEquals('QUL', $seg->qualifier());
        $this->assertEquals('NO_1', $seg->number());
        $this->assertEquals('1', $seg->priority());
        $this->assertEquals($seg->toString($delimiter), Loc::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
