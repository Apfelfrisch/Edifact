<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Pcd;
use Proengeno\Edifact\Test\TestCase;

final class PcdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Pcd::fromAttributes('30', '5');

        $this->assertEquals('PCD', $seg->name());
        $this->assertEquals('30', $seg->percent());
        $this->assertEquals('5', $seg->qualifier());
        $this->assertEquals($seg->toString($delimiter), Pcd::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
