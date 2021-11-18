<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Test\Message\Segments;

use DateTime;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unb;
use Proengeno\Edifact\Test\TestCase;

final class UnbTest extends TestCase
{
    /** @test */
    public function test_segment(): void
    {
        $seg = Unb::fromAttributes(new Delimiter(), '1234', '8', 'sender', '500', 'receiver', '600', new DateTime, 'ref-no', 'usage-type', '1');

        $this->assertEquals('UNB', $seg->name());
        $this->assertEquals('1234', $seg->syntaxId());
        $this->assertEquals('8', $seg->syntaxVersion());
        $this->assertEquals('sender', $seg->sender());
        $this->assertEquals('500', $seg->senderQualifier());
        $this->assertEquals('receiver', $seg->receiver());
        $this->assertEquals('600', $seg->receiverQualifier());
        $this->assertEquals((new DateTime)->format('YmdHi'), $seg->creationDateTime()->format('YmdHi'));
        $this->assertEquals('ref-no', $seg->referenzNumber());
        $this->assertEquals('usage-type', $seg->usageType());
        $this->assertEquals('1', $seg->testMarker());

        $this->assertEquals($seg->toString(), Unb::fromSegLine(new Delimiter(), $seg->toString()));
    }
}