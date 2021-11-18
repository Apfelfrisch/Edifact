<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ucd;
use Proengeno\Edifact\Test\TestCase;

final class UcdTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Ucd::fromAttributes(new Delimiter(), 'ECD', '66', '666');

        $this->assertEquals('UCD', $seg->name());
        $this->assertEquals('ECD', $seg->errorCode());
        $this->assertEquals('66', $seg->segmentPosition());
        $this->assertEquals('666', $seg->dataGroupPosition());
        $this->assertEquals($seg->toString(), Ucd::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
