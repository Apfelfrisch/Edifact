<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Segments;
use Apfelfrisch\Edifact\Stream;
use Apfelfrisch\Edifact\Test\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    public function setUp(): void
    {
        $file = new Stream(__DIR__ . '/data/edifact.txt');
        $this->message = new Message($file);
    }

    /** @test */
    public function test_instanciate_with_file_and_validator(): void
    {
        $this->assertInstanceOf(Message::class, $this->message);
    }

    /** @test */
    public function test_instanciate_from_a_string(): void
    {
        $message = Message::fromString("UNH");
        $this->assertInstanceOf(Message::class, $message);
    }

    /** @test */
    public function test_instanciate_from_a_filepath(): void
    {
        $message = Message::fromFilepath(__DIR__ . '/data/edifact.txt');
        $this->assertInstanceOf(Message::class, $message);
    }

    /** @test */
    public function test_string_casting(): void
    {
        $this->assertEquals("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'", (string)$this->message);
    }

    /** @test */
    public function test_array_casting(): void
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
        $this->assertEquals($array, $this->message->toArray());
    }

    /** @test */
    public function test_fetching_the_current_segement(): void
    {
        $message = Message::fromString("UNH'UNB");

        $this->assertInstanceOf(Segments\Unh::class, $message->getSegments()->getCurrent());
        $this->assertInstanceOf(Segments\Unh::class, $message->getSegments()->getCurrent());
    }

    /** @test */
    public function test_parsing_to_the_generic_segment_as_default_when_the_segment_is_unkown(): void
    {
        $message = Message::fromString("UKN");

        $this->assertInstanceOf(Segments\Generic::class, $message->getSegments()->getCurrent());
    }

    /** @test */
    public function test_throw_an_exception_if_no_fallback_was_set_and_the_segment_is_uknown(): void
    {
        $message = Message::fromString("UKN", SegmentFactory::withDefaultDegments(withFallback: false));

        $this->expectException(EdifactException::class);
        $message->getAllSegments();
    }

    /** @test */
    public function test_iterates_over_the_stream(): void
    {
        $message = Message::fromString("UNH'UNB'");

        $i = 0;
        foreach ($message->getSegments() as $segment) {
            $this->assertInstanceOf(SegInterface::class, $segment);
            $i++;
        }
        $this->assertSame(2, $i);
    }

    /** @test */
    public function test_finds_segments_by_class_name(): void
    {
        $message = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNT");

        $foundSegments = $message->filterAllSegments(Segments\Unh::class);

        $this->assertCount(2, $foundSegments);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[0]);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[1]);
    }

    /** @test */
    public function test_finds_segments_by_class_name_and_closure(): void
    {
        $message = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O11111+ORDERS:D:09B:UN:1.1e'UNT");

        $foundSegments = $message->filterAllSegments(Segments\Unh::class, fn(Segments\Unh $seg): bool =>
            $seg->reference() === 'O160482A7C2'
        );

        $this->assertCount(1, $foundSegments);
        $this->assertInstanceOf(Segments\Unh::class, $foundSegments[0]);
        $this->assertSame('O160482A7C2', $foundSegments[0]->reference());
    }

    /** @test */
    public function test_using_stream_filters(): void
    {
        $message = Message::fromString("FOO BAR");
        $message->addStreamFilter('string.tolower');

        $this->assertEquals("foo bar", (string)$message);
    }

    /** @test */
    public function test_getting_the_una_segement(): void
    {
        $unaValues = [":+.? '", "abcdef"];
        foreach ($unaValues as $unaValue) {
            $message = Message::fromString("UNA" . $unaValue . "UNH+'");
            $unaSegment = $message->getUnaSegment();
            $this->assertEquals($unaValue,
                 $unaSegment->componentSeparator()
               . $unaSegment->elementSeparator()
               . $unaSegment->decimalPoint()
               . $unaSegment->escapeCharacter()
               . $unaSegment->spaceCharacter()
               . $unaSegment->segmentTerminator()
           );
        }
    }

    /** @test */
    public function test_using_the_decimal_point_from_una(): void
    {
        $message = Message::fromString("UNA:+_? 'MOA+QUL:20_00'");

        /** @var Segments\Moa */
        $moa = $message->findFirstSegment(Segments\Moa::class);

        $this->assertSame('20.00', $moa->amount());
    }

    /** @test */
    public function test_unwrapping_the_message_with_the_default_header_and_trailer(): void
    {
        $message = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

        [$firstMessage, $secondMessage] = iterator_to_array($message->unwrap());

        $this->assertSame("UNH+1+ORDERS:D:96A:UN'UNT+2+1'", $firstMessage->toString());
        $this->assertSame("UNH+2+ORDERS:D:96A:UN'UNT+2+2'", $secondMessage->toString());
    }

    /** @test */
    public function test_providing_all_segments_as_an_array(): void
    {
        $message = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

        $this->assertCount(4, $message->getAllSegments());
        foreach ($message->getAllSegments() as $segment) {
            $this->assertInstanceOf(SegInterface::class, $segment);
        }
    }

    /** @test */
    public function test_escaping_string(): void
    {
        $message = Message::fromString("UNH+?:?+?''");

        $this->assertInstanceOf(SegInterface::class, $segment = $message->findFirstSegment(Segments\Unh::class));
        $this->assertSame(":+'", $segment->getValueFromPosition(1, 0));

        $message = Message::fromString("UNA|-.! _UNH-!|!-!__");

        $this->assertInstanceOf(SegInterface::class, $segment = $message->findFirstSegment(Segments\Unh::class));
        $this->assertSame("|-_", $segment->getValueFromPosition(1, 0));
    }
}
