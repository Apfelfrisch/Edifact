<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Bgm;
use Proengeno\Edifact\Test\TestCase;

final class BgmTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $seg = Bgm::fromAttributes(new Delimiter(), 'DCO', 'DNO', 'MCO');

        $this->assertEquals('BGM', $seg->name());
        $this->assertEquals('DCO', $seg->docCode());
        $this->assertEquals('DNO', $seg->docNumber());
        $this->assertEquals('MCO', $seg->messageCode());
        $this->assertEquals($seg->toString(), Bgm::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
