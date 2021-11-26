<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Doc;
use Apfelfrisch\Edifact\Test\TestCase;

final class DocTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Doc::fromAttributes('COD', 'NUMBER_1');

        $this->assertEquals('DOC', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals('NUMBER_1', $seg->number());
        $this->assertEquals($seg->toString($delimiter), Doc::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
