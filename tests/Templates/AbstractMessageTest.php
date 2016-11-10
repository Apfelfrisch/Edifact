<?php

namespace Proengeno\Edifact\Test\Templates;

use Mockery as m;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Test\Fixtures\Message;
use Proengeno\Edifact\Templates\AbstractMessage;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class AbstractMessageTest extends TestCase
{
    private $messageCore;

    public function setUp()
    {
        $file = new EdifactFile(__DIR__ . '/../data/edifact.txt');
        $this->messageCore = new Message($file, $this->getConfiguration());
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(AbstractMessage::class, $this->messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_string()
    {
        $messageCore = Message::fromString("UNH", $this->getConfiguration());
        $this->assertInstanceOf(AbstractMessage::class, $messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_filepath()
    {
        $messageCore = Message::fromFilepath(__DIR__ . '/../data/edifact.txt', $this->getConfiguration());
        $this->assertInstanceOf(AbstractMessage::class, $messageCore);
    }

    /** @test */
    public function it_can_cast_the_edifact_content_to_a_string()
    {
        $this->assertEquals("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'", (string)$this->messageCore);
    }

    /** @test */
    public function it_fetch_the_current_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB", $this->getConfiguration());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getCurrentSegment());
    }

    /** @test */
    public function it_fetch_the_next_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB", $this->getConfiguration());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_pinns_and_jumps_to_the_pointer_position()
    {
        $messageCore = Message::fromString("UNH'UNB", $this->getConfiguration());
        $messageCore->pinPointer();
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $messageCore->jumpToPinnedPointer();
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_jumps_to_the_actual_position_if_no_pointer_was_pinned()
    {
        $messageCore = Message::fromString("UNH'UNB", $this->getConfiguration());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $messageCore->jumpToPinnedPointer();
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_provides_the_count_of_the_parsed_segments()
    {
        $messageCore = Message::fromString("UNH'UNB", $this->getConfiguration());
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
        $messageCore->jumpToPinnedPointer();
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unb', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_iterates_over_the_stream()
    {
        $messageCore = Message::fromString("UNH'UNB'", $this->getConfiguration());
        $message = "";
        foreach ($messageCore as $segment) {
            $message .= (string)$segment;
        }
        $this->assertEquals($message, (string)$messageCore);
    }

    /** @test */
    public function it_fetch_a_specific_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNT", $this->getConfiguration());

        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unb',
            $messageCore->findNextSegment('UNB')
        );
        $this->assertFalse($messageCore->findNextSegment('UNH'));

    }

    /** @test */
    public function it_fetch_a_specific_segement_from_start_of_the_stream()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNT", $this->getConfiguration());
        $messageCore->findSegmentFromBeginn('UNH');

        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unh',
            $messageCore->findSegmentFromBeginn('UNH')
        );
        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unh',
            $messageCore->findSegmentFromBeginn('UNH', function($segment) {
                return $segment->referenz() == 'O160482A7C2';
            }
        ));
        $this->assertFalse(
            $messageCore->findSegmentFromBeginn('UNH', function($segment) {
                return $segment->referenz() == 'UNKNOW';
            })
        );
    }

    /** @test */
    public function it_can_validate_the_message()
    {
        $file = m::mock(EdifactFile::class, function($file) {
            $file->shouldReceive('rewind')->twice();
            $file->shouldReceive('getDelimiter')->once();
        });
        $configuration = new Configuration;
        $configuration->setMessageValidator(
            m::mock(MessageValidatorInterface::class, function($validator){
                $validator->shouldReceive('validate')->once();
            })
        );
        $messageCore = new Message($file, $configuration);
        $messageCore->validate();
    }

    /** @test */
    public function it_can_validate_the_message_segments()
    {
        $file = m::mock(EdifactFile::class, function($file) {
            $file->shouldReceive('rewind')->twice();
            $file->shouldReceive('getDelimiter')->once();
        });
        $configuration = new Configuration;
        $configuration->setMessageValidator(
            m::mock(MessageValidatorInterface::class, function($validator){
                $validator->shouldReceive('validate')->once();
            })
        );
        $messageCore = new Message($file, $configuration);
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
}
