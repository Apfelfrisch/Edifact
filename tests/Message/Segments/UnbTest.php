<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unb;

class UnbTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'UNB';
        $syntaxId = 'UNOC';
        $syntaxVersion = '3';
        $sender = '1234567890128';
        $senderQualifier = '500';
        $receiver = '1234567890128';
        $receiverQualifier = '14';
        $creationDatetime = new DateTime;
        $referenzNumber = 'ASDR13415';
        $usageType = 'TL';
        $testMarker = null;

        $seg = Unb::fromAttributes(
            $syntaxId,
            $syntaxVersion,
            $sender, 
            $senderQualifier, 
            $receiver, 
            $receiverQualifier, 
            $creationDatetime, 
            $referenzNumber, 
            $usageType, 
            $testMarker
        );


        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($syntaxId, $seg->syntaxId());
        $this->assertEquals($syntaxVersion, $seg->syntaxVersion());
        $this->assertEquals($sender, $seg->sender());
        $this->assertEquals($senderQualifier, $seg->senderQualifier());
        $this->assertEquals($receiver, $seg->receiver());
        $this->assertEquals($receiverQualifier, $seg->receiverQualifier());
        $this->assertEquals($creationDatetime->format('ymdhi'), $seg->creationDatetime()->format('ymdhi'));
        $this->assertEquals($referenzNumber, $seg->referenzNumber());
        $this->assertEquals($usageType, $seg->usageType());
        $this->assertEquals($testMarker, $seg->testMarker());
        //die(var_dump($seg));
    }
}
