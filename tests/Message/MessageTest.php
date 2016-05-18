<?php

namespace Proengeno\Edifact\Test\Message;

use Mockery as m;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Test\Fixtures\Message;
use Proengeno\Edifact\Message\Message as MessageCore;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class MessageTest extends TestCase 
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
    public function it_fetch_the_current_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB");
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->getCurrentSegment());
    }

    /** @test */
    public function it_fetch_the_next_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB");
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_fetch_a_specific_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB'UNT");
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unb', $messageCore->findNextSegment('UNB'));
        $this->assertFalse($messageCore->findNextSegment('UNH'));
        $this->assertInstanceOf('Proengeno\Edifact\Message\Segments\Unh', $messageCore->findNextSegment('UNH', $fromFileStart = true));
    }

    /** @test */
    public function it_can_validate_itself()
    {
        $file = m::mock(EdifactFile::class);
        $validator = m::mock(MessageValidatorInterface::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });
        $messageCore = new Message($file, $validator);
        $messageCore->validate();
    }

    /** @test */
    public function it_can_return_the_delimter()
    {
        $validator = m::mock(MessageValidatorInterface::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });

        $unaValues = [":+.? '", "abcdef"];
        foreach ($unaValues as $unaValue) {
            $messageCore = Message::fromString("UNA" . $unaValue . "'UNH");
            $delimiter = $messageCore->getDelimiter();
            $this->assertEquals($unaValue, 
                 $delimiter->getData()
               . $delimiter->getDataGroup()
               . $delimiter->getDecimal()
               . $delimiter->getTerminator()
               . $delimiter->getEmpty()
               . $delimiter->getSegment()
           );
        }
    }

    /** @test */
    public function it_return_the_message_builder_class()
    {
        $message = Message::build('from', 'to');

        $this->assertInstanceOf('Proengeno\Edifact\Message\Builder', $message);
    }
}
    
