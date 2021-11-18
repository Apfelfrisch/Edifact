<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ucm;
use Proengeno\Edifact\Test\TestCase;

final class UcmTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Ucm::fromAttributes(new Delimiter(), 'ref', 'TYP', 'VNO', 'RNO', 'ORG', 'OCD', 'ECD', 'SEG', 'SPO', 'DPO');

        $this->assertEquals('UCM', $seg->name());
        $this->assertEquals('ref', $seg->referenz());
        $this->assertEquals('TYP', $seg->type());
        $this->assertEquals('VNO', $seg->versionNumber());
        $this->assertEquals('RNO', $seg->releaseNumber());
        $this->assertEquals('ORG', $seg->organisation());
        $this->assertEquals('OCD', $seg->organisationCode());
        $this->assertEquals('ECD', $seg->errorCode());
        $this->assertEquals('SEG', $seg->serviceSegement());
        $this->assertEquals('SPO', $seg->segmentPosition());
        $this->assertEquals('DPO', $seg->dataGroupPosition());

        $this->assertEquals($seg->toString(), Ucm::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
