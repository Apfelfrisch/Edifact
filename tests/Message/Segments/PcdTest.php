<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Pcd;
use Proengeno\Edifact\Test\TestCase;

final class PcdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Pcd::fromAttributes(new Delimiter(), '30', '5');

        $this->assertEquals('PCD', $seg->name());
        $this->assertEquals('30', $seg->percent());
        $this->assertEquals('5', $seg->qualifier());
        $this->assertEquals($seg->toString(), Pcd::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
