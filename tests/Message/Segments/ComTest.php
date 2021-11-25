<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Message\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Com;
use Apfelfrisch\Edifact\Test\TestCase;

final class ComTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Com::fromAttributes('ID', 'TYP');

        $this->assertEquals('COM', $seg->name());
        $this->assertEquals('ID', $seg->id());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals($seg->toString($delimiter), Com::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
