<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Bgm;
use Proengeno\Edifact\Test\TestCase;

final class BgmTest extends TestCase
{
    /** @test */
    public function test_ajt_segment()
    {
        $delimiter = new Delimiter();
        $seg = Bgm::fromAttributes('DCO', 'DNO', 'MCO');

        $this->assertEquals('BGM', $seg->name());
        $this->assertEquals('DCO', $seg->docCode());
        $this->assertEquals('DNO', $seg->docNumber());
        $this->assertEquals('MCO', $seg->messageCode());
        $this->assertEquals($seg->toString($delimiter), Bgm::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}
