<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Ajt;
use Proengeno\Edifact\Test\TestCase;

final class AjtTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Ajt::fromAttributes('COD');

        $this->assertEquals('AJT', $seg->name());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString($delimiter), Ajt::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
