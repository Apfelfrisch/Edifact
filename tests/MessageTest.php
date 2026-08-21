<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\StreamMessageFactory;
use Apfelfrisch\Edifact\Test\Fixtures\Moa;
use Apfelfrisch\Edifact\Test\Fixtures\Rff;
use Apfelfrisch\Edifact\Test\Fixtures\Unh;
use Override;

final class MessageTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        new StreamMessageFactory()
            ->addSegment('UNH', Unh::class)
            ->addSegment('RFF', Rff::class)
            ->addSegment('MOA', Moa::class)
            ->addFallback(GenericSegment::class)
            ->markAsDefault();
    }

    #[Override]
    protected function tearDown(): void
    {
        new StreamMessageFactory()->markAsDefault();
    }

    public function test_instanciate_from_a_string(): void
    {
        $message = Message::fromString('UNH');
        static::assertInstanceOf(Message::class, $message);
    }

    public function test_materialized_segments_are_reused(): void
    {
        $message = Message::fromString("UNH+1'RFF+Z13:17103'");

        static::assertSame($message->getAllSegments(), $message->getAllSegments());
        static::assertSame("UNH+1'RFF+Z13:17103'", $message->toString());
        static::assertSame("UNH+1'RFF+Z13:17103'", $message->toString());
        static::assertSame($message->getAllSegments(), iterator_to_array($message->getSegments(), false));
    }

    public function test_instanciate_from_a_filepath(): void
    {
        $message = Message::fromFilepath(__DIR__ . '/data/edifact.txt');
        static::assertInstanceOf(Message::class, $message);
    }

    public function test_string_casting(): void
    {
        $message = Message::fromFilepath(__DIR__ . '/data/edifact.txt');
        static::assertSame("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'", (string) $message);
    }

    public function test_array_casting(): void
    {
        $message = Message::fromFilepath(__DIR__ . '/data/edifact.txt');
        $array = [
            [
                'UNH' => ['UNH' => 'UNH'],
                '0062' => ['0062' => 'O160482A7C2'],
                'S009' => ['0065' => 'ORDERS', '0052' => 'D', '0054' => '09B', '0051' => 'UN', '0057' => '1.1e'],
            ],
            [
                'RFF' => ['RFF' => 'RFF'],
                'C506' => [1153 => 'Z13', 1154 => '17103'],
            ],
        ];

        static::assertEquals($array, $message->toArray());
    }

    public function test_fetching_the_current_segement(): void
    {
        $message = Message::fromString("UNH'UNB");

        static::assertInstanceOf(Unh::class, $message->getSegments()->current());
        static::assertInstanceOf(Unh::class, $message->getSegments()->current());
        static::assertSame(0, $message->getSegments()->key());
    }

    public function test_parsing_to_the_generic_segment_as_default_when_the_segment_is_unknown(): void
    {
        $message = Message::fromString('UKN');

        static::assertInstanceOf(GenericSegment::class, $message->getSegments()->current());
    }

    public function test_throw_an_exception_if_no_fallback_was_set_and_the_segment_is_unknown(): void
    {
        $message = new StreamMessageFactory()->fromString('UKN');

        $this->expectException(InvalidEdifactContentException::class);
        $message->getAllSegments();
    }

    public function test_iterates_over_the_stream(): void
    {
        $message = Message::fromString("UNH'UNB'");

        $i = 0;

        foreach ($message->getSegments() as $segment) {
            static::assertInstanceOf(Segment::class, $segment);
            $i++;
        }

        static::assertSame(2, $i);
    }

    public function test_finds_segments_by_class_name(): void
    {
        $message = Message::fromString(
            "UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNT",
        );

        $foundSegments = [];

        foreach ($message->filterSegments(Unh::class) as $segment) {
            static::assertInstanceOf(Unh::class, $segment);
            $foundSegments[] = $segment;
        }

        static::assertCount(2, $foundSegments);
        static::assertEquals($foundSegments, $message->filterAllSegments(Unh::class));
    }

    public function test_finds_segments_by_class_name_and_closure(): void
    {
        $message = Message::fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'UNB'UNH+O11111+ORDERS:D:09B:UN:1.1e'UNT");

        $foundSegments = $message->filterAllSegments(
            Unh::class,
            static fn(Unh $seg): bool => $seg->reference() === 'O160482A7C2',
        );

        static::assertCount(1, $foundSegments);
        static::assertInstanceOf(Unh::class, $foundSegments[0]);
        static::assertSame('O160482A7C2', $foundSegments[0]->reference());
    }

    public function test_using_stream_filters(): void
    {
        $message = new StreamMessageFactory()
            ->addFallback(GenericSegment::class)
            ->addStreamFilter('string.tolower')
            ->fromString("FOO BAR'");

        static::assertSame("foo bar'", (string) $message);
    }

    public function test_getting_the_una_segement(): void
    {
        $unaValues = [":+.? '", 'abcdef'];

        foreach ($unaValues as $unaValue) {
            $message = Message::fromString('UNA' . $unaValue . "UNH+'");
            $unaSegment = $message->getUnaSegment();
            static::assertSame(
                $unaValue,
                $unaSegment->componentSeparator()
                    . $unaSegment->elementSeparator()
                    . $unaSegment->decimalPoint()
                    . $unaSegment->escapeCharacter()
                    . $unaSegment->spaceCharacter()
                    . $unaSegment->segmentTerminator(),
            );
        }
    }

    public function test_using_the_decimal_point_from_una(): void
    {
        $message = Message::fromString("UNA:+_? 'MOA+QUL:20_00'");

        $moa = $message->findFirstSegment(Moa::class);

        static::assertInstanceOf(Moa::class, $moa);
        static::assertSame('20.00', $moa->amount());
    }

    public function test_unwrapping_the_message_with_the_default_header_and_trailer(): void
    {
        $message = Message::fromString(
            "UNA:+.? 'UNH+1+ORDERS:D:96A:UN'NAD+DP++++Auf m Rott?''UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'",
        );

        [$firstMessage, $secondMessage] = iterator_to_array($message->unwrap());

        static::assertSame("UNH+1+ORDERS:D:96A:UN'NAD+DP++++Auf m Rott?''UNT+2+1'", $firstMessage->toString());
        static::assertSame("UNH+2+ORDERS:D:96A:UN'UNT+2+2'", $secondMessage->toString());
    }

    public function test_providing_all_segments_as_an_array(): void
    {
        $message = Message::fromString("UNA:+.? 'UNH+1+ORDERS:D:96A:UN'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'");

        static::assertCount(4, $message->getAllSegments());

        foreach ($message->getAllSegments() as $segment) {
            static::assertInstanceOf(Segment::class, $segment);
        }
    }

    public function test_escaping_string(): void
    {
        $message = Message::fromString("UNH+?:?+?''");

        static::assertInstanceOf(Segment::class, $segment = $message->findFirstSegment(Unh::class));
        static::assertSame(":+'", $segment->getValueFromPosition(1, 0));

        $message = Message::fromString('UNA|-.! _UNH-!|!-!__');

        static::assertInstanceOf(Segment::class, $segment = $message->findFirstSegment(Unh::class));
        static::assertSame('|-_', $segment->getValueFromPosition(1, 0));
    }

    public function test_find_first_segment_returns_null_when_nothing_matches(): void
    {
        $message = Message::fromString("UNB'");

        static::assertNull($message->findFirstSegment(Unh::class));
    }

    public function test_filter_segments_with_a_closure_that_matches_nothing(): void
    {
        $message = Message::fromString("UNH+A'UNH+B'");

        static::assertSame(
            [],
            $message->filterAllSegments(Unh::class, static fn(Unh $seg): bool => $seg->reference() === 'C'),
        );
    }

    public function test_unwrapping_with_custom_header_and_trailer(): void
    {
        $message = Message::fromString("HDR+1'SEQ+1'TRL+1'HDR+2'TRL+2'");

        $partials = iterator_to_array($message->unwrap('HDR', 'TRL'));

        static::assertCount(2, $partials);
        static::assertSame("HDR+1'SEQ+1'TRL+1'", $partials[0]->toString());
        static::assertSame("HDR+2'TRL+2'", $partials[1]->toString());
    }

    public function test_unwrapping_yields_nothing_without_a_trailer(): void
    {
        $message = Message::fromString("UNH+1'SEQ+1'");

        static::assertSame([], iterator_to_array($message->unwrap()));
    }

    public function test_unwrapping_ignores_segments_outside_of_header_and_trailer(): void
    {
        $message = Message::fromString("UNB+1'UNH+1'UNT+1'UNZ+1'");

        $partials = iterator_to_array($message->unwrap());

        static::assertCount(1, $partials);
        static::assertSame("UNH+1'UNT+1'", $partials[0]->toString());
    }

    public function test_marked_default_factory_is_returned(): void
    {
        $factory = new StreamMessageFactory();
        $factory->markAsDefault();

        static::assertSame($factory, Message::getFactory());
    }
}
