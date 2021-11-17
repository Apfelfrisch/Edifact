<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cni;
use Proengeno\Edifact\Test\TestCase;

final class CniTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Cni::fromAttributes(new Delimiter(), '12345');

        $this->assertEquals('CNI', $seg->name());
        $this->assertEquals('12345', $seg->number());
        $this->assertEquals($seg->toString(), Cni::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
