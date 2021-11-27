<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Ucd;
use Apfelfrisch\Edifact\Test\TestCase;

final class UcdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Ucd::fromAttributes('ECD', '66', '666');

        $this->assertEquals('UCD', $seg->name());
        $this->assertEquals('ECD', $seg->errorCode());
        $this->assertEquals('66', $seg->segmentPosition());
        $this->assertEquals('666', $seg->elementPosition());
        $this->assertEquals($seg->toString($delimiter), Ucd::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
