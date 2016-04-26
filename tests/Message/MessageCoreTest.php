<?php

use Mockery as m;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\MessageCore;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class MessageCoreTest extends TestCase 
{
    private $messageCore;

    public function setUp()
    {
        $file = new EdifactFile(__DIR__ . '/../data/edifact.txt');
        $validator = m::mock(MessageValidatorInterface::class);
        $this->messageCore = new Message($file, $validator);
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(MessageCore::class, $this->messageCore);
    }

    /** @test */
    public function it_instanciates_with_string()
    {
        $messageCore = Message::fromString("UNH");
        $this->assertEquals('UNH', (string)$messageCore);
    }

    /** @test */
    public function it_can_cast_the_edifact_content_to_a_string()
    {
        $this->assertEquals("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'", (string)$this->messageCore);
    }

    /** @test */
    public function it_fetch_the_next_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB");
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_fetch_the_previous_segement_from_stream()
    {
        $messageCore = Message::fromString("UNB'UNH'RFF");
        $messageCore->getNextSegment();
        $messageCore->getNextSegment();
        $messageCore->getNextSegment();
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->getPreviousSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unb', $messageCore->getPreviousSegment());
    }

    /** @test */
    public function it_can_call_a_validator_on_itself()
    {
        $file = m::mock(EdifactFile::class);
        $validator = m::mock(MessageValidatorInterface::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });
        $messageCore = new Message($file, $validator);
        $messageCore->validate();
    }
}
    
