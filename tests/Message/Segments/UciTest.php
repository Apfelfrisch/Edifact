<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Uci;
use Proengeno\Edifact\Test\TestCase;

final class UciTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Uci::fromAttributes(new Delimiter(), 'UNB-ref', 'sender', '500', 'receiver', '500', 'SCD', 'ECD', 'SEG', 'SPO', 'EPO');

        $this->assertEquals('UCI', $seg->name());
        $this->assertEquals('UNB-ref', $seg->unbRef());
        $this->assertEquals('sender', $seg->sender());
        $this->assertEquals('500', $seg->senderCode());
        $this->assertEquals('receiver', $seg->receiver());
        $this->assertEquals('500', $seg->receiverCode());
        $this->assertEquals('SCD', $seg->statusCode());
        $this->assertEquals('ECD', $seg->errorCode());
        $this->assertEquals('SEG', $seg->serviceSegement());
        $this->assertEquals('SPO', $seg->segmentPosition());
        $this->assertEquals('EPO', $seg->elementPosition());

        $this->assertEquals($seg->toString(), Uci::fromSegLine(new Delimiter(), $seg->toString()));
    }
}
