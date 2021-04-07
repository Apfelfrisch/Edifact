<?php

namespace Proengeno\Edifact\Test\Message;

use Mockery as m;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Validation\MessageValidator;

class MessageTest extends TestCase
{
    private $messageCore;

    public function setUp(): void
    {
        $file = new EdifactFile(__DIR__ . '/../data/edifact.txt');
        $this->messageCore = new Message($file, $this->getConfiguration(), $this->getDescriber());
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(Message::class, $this->messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_string()
    {
        $messageCore = Message::fromString("UNH", $this->getConfiguration());
        $this->assertInstanceOf(Message::class, $messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_filepath()
    {
        $messageCore = Message::fromFilepath(__DIR__ . '/../data/edifact.txt', $this->getConfiguration());
        $this->assertInstanceOf(Message::class, $messageCore);
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
    public function it_parses_to_a_generic_segment_if_a_segment_is_unkown()
    {
        $messageCore = Message::fromString("UKN", $this->getConfiguration());

        $this->assertInstanceOf('Proengeno\Edifact\Message\GenericSegment', $messageCore->getNextSegment());
    }

    /** @test */
    public function it_throw_an_exception_if_no_generic_segment_is_set_and_a_segment_is_uknown()
    {
        $configuration = $this->getConfiguration();
        $configuration->setGenericSegment(null);
        $messageCore = Message::fromString("UKN", $configuration);

        $this->expectException('Proengeno\Edifact\Exceptions\ValidationException');
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $messageCore->getNextSegment());
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
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UKN'UNT", $this->getConfiguration());

        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unb',
            $messageCore->findNextSegment('UNB')
        );
        $this->assertInstanceOf(
            'Proengeno\Edifact\Message\GenericSegment',
            $messageCore->findNextSegment('UKN')
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
        $this->assertInstanceOf(
            'Proengeno\Edifact\Test\Fixtures\Segments\Unh',
            $messageCore->findSegmentFromBeginn('UNH', ['referenz' => 'O160482A7C2'])
        );
        $this->assertFalse(
            $messageCore->findSegmentFromBeginn('UNH', function($segment) {
                return $segment->referenz() == 'UNKNOW';
            })
        );
    }

    /** @test */
    public function it_can_close_the_ressource()
    {
        $messageCore = Message::fromString("FOO BAR", $this->getConfiguration());
        $messageCore->closeStream();
        $this->assertEquals("", (string)$messageCore);
    }

    /** @test */
    public function it_rereads_the_stream_after_accessing_closed_stream()
    {
        $this->messageCore->closeStream();
        $this->assertInstanceOf('Proengeno\Edifact\Test\Fixtures\Segments\Unh', $this->messageCore->getCurrentSegment());
    }

    /** @test */
    public function it_uses_the_filters_from_configuration_class()
    {
        $configuration = $this->getConfiguration();
        $configuration->setWriteFilter(function($content) {
            return str_replace("F", "X", $content);
        });
        $configuration->setReadFilter(function($content) {
            return str_replace("B", "X", $content);
        });

        $messageCore = Message::fromString("FOO BAR", $configuration);

        $this->assertEquals("XOO XAR", (string)$messageCore);
    }

    /** @test */
    public function it_can_validate_the_message()
    {
        $file = m::mock(EdifactFile::class, function($file) {
            $file->shouldReceive('rewind');
            $file->shouldReceive('getDelimiter')->once();
            $file->shouldReceive('close');
        });
        $validator = m::mock(MessageValidator::class, function($validator){
            $validator->shouldReceive('validate')->once();
        });
        $messageCore = new Message($file, $this->getConfiguration(), $this->getDescriber());
        $this->assertInstanceOf(get_class($messageCore), $messageCore->validate($validator));
    }

    /** @test */
    public function it_can_return_the_delimter()
    {
        $unaValues = [":+.? '", "abcdef"];
        foreach ($unaValues as $unaValue) {
            $messageCore = Message::fromString("UNA" . $unaValue . "UNH+'", $this->getConfiguration());
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
