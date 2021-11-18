<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Alc;
use Proengeno\Edifact\Test\TestCase;

final class AlcTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Alc::fromAttributes('QAL', 'COD');

        $this->assertEquals('ALC', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString($delimiter), Alc::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
