<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Test\Segments;

use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Ucm;
use Apfelfrisch\Edifact\Test\TestCase;

final class UcmTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $delimiter = new Delimiter();
        $seg = Ucm::fromAttributes('ref', 'TYP', 'VNO', 'RNO', 'ORG', 'OCD', 'ECD', 'SEG', 'SPO', 'DPO');

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

        $this->assertEquals($seg->toString($delimiter), Ucm::fromSegLine($delimiter, $seg->toString($delimiter))->toString($delimiter));
    }
}