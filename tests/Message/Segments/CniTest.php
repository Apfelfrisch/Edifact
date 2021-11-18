<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Cni;
use Proengeno\Edifact\Test\TestCase;

final class CniTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Cni::fromAttributes('12345');

        $this->assertEquals('CNI', $seg->name());
        $this->assertEquals('12345', $seg->number());
        $this->assertEquals($seg->toString($delimiter), Cni::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
