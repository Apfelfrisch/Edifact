<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Com;
use Proengeno\Edifact\Test\TestCase;

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
