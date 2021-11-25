<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Generic;
use Apfelfrisch\Edifact\Test\TestCase;

final class GenericTest extends TestCase
{
    /** @test */
    public function test_segment()
    {
        $delimiter = new Delimiter();
        $seg = Generic::fromAttributes(['TST'], ['a', '1', '2'], ['b', '1']);

        $this->assertEquals('TST', $seg->name());
        $this->assertEquals($seg->toString($delimiter), Generic::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
