<?php

use Mockery as m;
use Proengeno\Edifact\Message\MessageCore;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class MessageCoreTest extends TestCase 
{
    /** @test */
    public function it_instanciates_from_an_edifact_string()
    {
        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");
    }

    /** @test */
    public function it_can_search_the_whole_message_segments_by_name()
    {
        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");

        $this->assertCount(1, $messageCore->findSegments('UNH'));
    }

    /** @test */
    public function it_can_search_the_a_specific_sub_message_segments_by_name()
    {
        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");

        $this->assertCount(1, $messageCore->findSegments('UNH', 0));
    }

    /** @test */
    public function it_can_search_the_a_specific_sub_message_body_segments_by_name()
    {
        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");

        $this->assertCount(1, $messageCore->findSegments('BGM', 0, 0));
    }

    /** @test */
    public function it_can_search_the_a_specific_message_and_returns_an_empty_array_if_nothing_was_found()
    {
        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");

        $this->assertCount(0, $messageCore->findSegments('AGR'));
    }

    /** @test */
    public function it_can_call_a_validator_on_itself()
    {
        $validator = m::mock(MessageValidatorInterface::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });

        $messageCore = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'", $validator);
        $messageCore->validate();
    }

    /** @test */
    public function it_can_cast_itself_to_a_string()
    {
        $edifactString = "UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'";
        $messageCore = MessageDummy::fromString($edifactString);

        $this->assertEquals($edifactString, (string)$messageCore);
    }

}
    
