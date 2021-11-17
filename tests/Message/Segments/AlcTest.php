<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Alc;
use Proengeno\Edifact\Test\TestCase;

final class AlcTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Alc::fromAttributes(new Delimiter(), 'QAL', 'COD');

        $this->assertEquals('ALC', $seg->name());
        $this->assertEquals('QAL', $seg->qualifier());
        $this->assertEquals('COD', $seg->code());
        $this->assertEquals($seg->toString(), Alc::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
