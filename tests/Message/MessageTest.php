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
        $this->messageCore = new Message($file, m::mock(MessageValidatorInterface::class));
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
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getCurrentSegment());
    }

    /** @test */
    public function it_fetch_the_next_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB");
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_fetch_a_specific_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNT");

        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unb', 
            $messageCore->findNextSegment('UNB')
        );
        $this->assertFalse($messageCore->findNextSegment('UNH'));

        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unh', 
            $messageCore->findNextSegment('UNH', $fromFileStart = true)
        );
        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unh', 
            $messageCore->findNextSegment('UNH', $fromFileStart = true, function($segment) {
                return $segment->referenz() == 'O160482A7C2';
            }
        ));
        $this->assertFalse(
            $messageCore->findNextSegment('UNH', $fromFileStart = true, function($segment) {
                return $segment->referenz() == 'UNKNOW';
            })
        );
    }

    /** @test */
    public function it_can_validate_itself()
    {
        $file = m::mock(EdifactFile::class, function($file) {
            $file->shouldReceive('rewind')->twice();
            $file->shouldReceive('getDelimiter')->once();
        });
        $validator = m::mock(MessageValidatorInterface::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });
        $messageCore = new Message($file, $validator);
        $messageCore->validate();
    }

    /** @test */
    public function it_can_return_the_delimter()
    {
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
        $message = Message::build('from', 'to', './');

        $this->assertInstanceOf('Proengeno\Edifact\Message\Builder', $message);
    }
}
    
