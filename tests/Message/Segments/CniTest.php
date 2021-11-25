<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Cni;
use Apfelfrisch\Edifact\Test\TestCase;

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
