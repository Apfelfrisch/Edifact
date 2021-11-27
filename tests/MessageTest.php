<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Exceptions\SegValidationException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments;
use Apfelfrisch\Edifact\Stream;
use Apfelfrisch\Edifact\Test\TestCase;

class MessageTest extends TestCase
{
    private $messageCore;

    public function setUp(): void
    {
        $file = new Stream(__DIR__ . '/data/edifact.txt');
        $this->messageCore = new Message($file);
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(Message::class, $this->messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_string()
    {
        $messageCore = Message::fromString("UNH");
        $this->assertInstanceOf(Message::class, $messageCore);
    }

    /** @test */
    public function it_instanciates_from_a_filepath()
    {
        $messageCore = Message::fromFilepath(__DIR__ . '/data/edifact.txt');
        $this->assertInstanceOf(Message::class, $messageCore);
    }

    /** @test */
    public function it_can_cast_the_edifact_content_to_a_string()
    {
        $this->assertEquals("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'", (string)$this->messageCore);
    }

    /** @test */
    public function it_can_cast_the_edifact_content_to_an_array()
    {
        $array = [
            [
              "UNH" => ["UNH" => "UNH"],
              "0062" => ["0062" => "O160482A7C2"],
              "S009" => ["0065" => "ORDERS", "0052" => "D", "0054" => "09B", "0051" => "UN", "0057" => "1.1e"],
            ],
            [
              "RFF" => ["RFF" => "RFF"],
              "C506" => [1153 => "Z13", 1154 => "17103"],
            ]
        ];
        $this->assertEquals($array, $this->messageCore->toArray());
    }

    /** @test */
    public function it_fetch_the_current_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH'UNB");

        $this->assertInstanceOf(Segments\Unh::class, $messageCore->getSegments()->getCurrent());
        $this->assertInstanceOf(Segments\Unh::class, $messageCore->getSegments()->getCurrent());
    }

    /** @test */
    public function it_parses_to_a_generic_segment_if_a_segment_is_unkown()
    {
        $messageCore = Message::fromString("UKN");

        $this->assertInstanceOf(Segments\Generic::class, $messageCore->getSegments()->getCurrent());
    }

    /** @test */
    public function it_throw_an_exception_if_no_generic_segment_is_set_and_a_segment_is_uknown()
    {
        $messageCore = Message::fromString("UKN", SegmentFactory::withDefaultDegments(withFallback: false));

        $this->expectException(SegValidationException::class);
        $messageCore->getAllSegments();
    }

    /** @test */
    public function it_iterates_over_the_stream()
    {
        $messageCore = Message::fromString("UNH'UNB'");
        $message = "";
        foreach ($messageCore->getSegments() as $segment) {
            $message .= $segment->toString($messageCore->getDelimiter()) . $messageCore->getDelimiter()->getSegmentTerminator();
        }
        $this->assertEquals($message, (string)$messageCore);
    }

    /** @test */
    public function it_fetch_a_specific_segement_from_stream()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UKN'UNT");

        $this->assertInstanceOf(
            Segments\Unb::class,
            $messageCore->findFirstSegment(Segments\Unb::class)
        );
        $this->assertInstanceOf(
            Segments\Generic::class,
            $messageCore->findFirstSegment(Segments\Generic::class, fn(Segments\Generic $seg): bool => $seg->name() === 'UKN')
        );
        $this->assertNull($messageCore->findFirstSegment(Segments\Seq::class));
    }

    /** @test */
    public function it_finds_segments_by_class_name()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNT");

        $foundSegments = $messageCore->filterAllSegments(Segments\Unh::class);

        $this->assertCount(2, $foundSegments);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[0]);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[1]);
    }

    /** @test */
    public function it_finds_segments_by_class_name_and_closure()
    {
        $messageCore = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O11111+ORDERS:D:09B:UN:1.1e'UNT");

        $foundSegments = $messageCore->filterAllSegments(Segments\Unh::class, fn(Segments\Unh $seg): bool =>
            $seg->reference() === 'O160482A7C2'
        );

        $this->assertCount(1, $foundSegments);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[0]);
        $this->assertSame('O160482A7C2', $foundSegments[0]->reference());
    }

    /** @test */
    public function it_uses_the_filters_from_configuration_class()
    {
        $messageCore = Message::fromString("FOO BAR");
        $messageCore->addStreamFilter('string.tolower');

        $this->assertEquals("foo bar", (string)$messageCore);
    }

    /** @test */
    public function it_can_return_the_delimter()
    {
        $unaValues = [":+.? '", "abcdef"];
        foreach ($unaValues as $unaValue) {
            $messageCore = Message::fromString("UNA" . $unaValue . "UNH+'");
            $delimiter = $messageCore->getDelimiter();
            $this->assertEquals($unaValue,
                 $delimiter->getComponentSeparator()
               . $delimiter->getElementSeparator()
               . $delimiter->getDecimalPoint()
               . $delimiter->getEscapeCharacter()
               . $delimiter->getSpaceCharacter()
               . $delimiter->getSegmentTerminator()
           );
        }
    }

    /** @test */
    public function it_converts_decimal_seperator()
    {
        $message = Message::fromString("UNA:+_? 'MOA+QUL:20_00'");

        /** @var Segments\Moa */
        $moa = $message->findFirstSegment(Segments\Moa::class);

        $this->assertSame('20.00', $moa->amount());
    }

    /** @test */
    public function it_unwraps_the_message_with_the_default_header_and_trailer()
    {
        $messageCore = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

        [$firstMessage, $secondMessage] = iterator_to_array($messageCore->unwrap());

        $this->assertSame("UNH+1+ORDERS:D:96A:UN'UNT+2+1'", $firstMessage->toString());
        $this->assertSame("UNH+2+ORDERS:D:96A:UN'UNT+2+2'", $secondMessage->toString());
    }

    /** @test */
    public function it_provdes_all_segments()
    {
        $messageCore = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

        $this->assertCount(5, $messageCore->getAllSegments());
        foreach ($messageCore->getAllSegments() as $segment) {
            $this->assertInstanceOf(SegInterface::class, $segment);
        }
    }
}
