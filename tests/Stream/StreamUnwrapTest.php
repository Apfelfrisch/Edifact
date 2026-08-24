<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Stream;

use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class StreamUnwrapTest extends TestCase
{
    public function test_unwrapping_with_the_default_header_and_trailer(): void
    {
        $stream = Stream::fromString(
            "UNH+1+ORDERS:D:96A:UN'NAD+DP++++Musterstr.'UNT+2+1'UNH+2+ORDERS:D:96A:UN'UNT+2+2'",
        );

        static::assertSame(
            ["UNH+1+ORDERS:D:96A:UN'NAD+DP++++Musterstr.'UNT+2+1'", "UNH+2+ORDERS:D:96A:UN'UNT+2+2'"],
            $this->contents($stream->unwrap()),
        );
    }

    public function test_unwrapping_with_custom_header_and_trailer(): void
    {
        $stream = Stream::fromString("HDR+1'SEQ+1'TRL+1'HDR+2'TRL+2'");

        static::assertSame(["HDR+1'SEQ+1'TRL+1'", "HDR+2'TRL+2'"], $this->contents($stream->unwrap('HDR', 'TRL')));
    }

    public function test_unwrapping_yields_nothing_without_a_trailer(): void
    {
        $stream = Stream::fromString("UNH+1'SEQ+1'");

        static::assertSame([], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_ignores_segments_outside_of_header_and_trailer(): void
    {
        $stream = Stream::fromString("UNB+1'UNH+1'UNT+1'UNZ+1'");

        static::assertSame(["UNH+1'UNT+1'"], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_ignores_a_trailer_before_the_first_header(): void
    {
        $stream = Stream::fromString("UNT+9'UNH+1'UNT+2'");

        static::assertSame(["UNH+1'UNT+2'"], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_keeps_escaped_segment_terminators_escaped(): void
    {
        $stream = Stream::fromString("UNH+1'NAD+DP++++Auf m Rott?''UNT+2+1'");

        static::assertSame(["UNH+1'NAD+DP++++Auf m Rott?''UNT+2+1'"], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_writes_the_una_segment_into_every_partial(): void
    {
        $stream = Stream::fromString("UNA:+.? 'UNH+1'UNT+1'UNH+2'UNT+2'");

        static::assertSame(["UNA:+.? 'UNH+1'UNT+1'", "UNA:+.? 'UNH+2'UNT+2'"], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_keeps_custom_una_characters(): void
    {
        $stream = Stream::fromString('UNA|#,! _UNH#1|A!__UNT#1_UNH#2_UNT#2_');

        $partials = iterator_to_array($stream->unwrap());

        static::assertCount(2, $partials);
        static::assertSame('UNA|#,! _UNH#1|A!__UNT#1_', $partials[0]->toString());
        static::assertSame('UNA|#,! _UNH#2_UNT#2_', $partials[1]->toString());
        static::assertSame('_', $partials[0]->getUnaSegment()->segmentTerminator());

        $partials[0]->rewind();
        static::assertSame('UNA|#,! ', $partials[0]->getSegment());
        static::assertSame('UNH#1|A_', $partials[0]->getSegment());
        static::assertSame('UNT#1', $partials[0]->getSegment());
    }

    public function test_unwrapping_keeps_custom_una_characters_without_a_una_segment_in_the_stream(): void
    {
        $stream = new Stream('php://temp', 'w+', new UnaSegment('|', '#', ',', '!', ' ', '_'));
        $stream->writeAndRewind('UNH#1_UNT#1_');

        $partials = iterator_to_array($stream->unwrap());

        static::assertCount(1, $partials);
        static::assertSame('UNH#1_UNT#1_', $partials[0]->toString());
        static::assertSame('_', $partials[0]->getUnaSegment()->segmentTerminator());
    }

    public function test_partials_are_round_trippable_streams(): void
    {
        $stream = Stream::fromString("UNA:+.? 'UNH+1'NAD+Rott?''UNT+1'");

        [$partial] = iterator_to_array($stream->unwrap());
        $copy = Stream::fromString($partial->toString());

        static::assertSame('UNA:+.? ', $copy->getSegment());
        static::assertSame('UNH+1', $copy->getSegment());
        static::assertSame("NAD+Rott'", $copy->getSegment());
        static::assertSame('UNT+1', $copy->getSegment());
    }

    public function test_unwrapping_applies_the_read_filters_of_the_source(): void
    {
        $stream = Stream::fromString("UNH+1'NAD+\xE4\xF6\xFC'UNT+1'");
        $stream->addReadFilter('convert.iconv.ISO-8859-1.UTF-8');

        static::assertSame(["UNH+1'NAD+äöü'UNT+1'"], $this->contents($stream->unwrap()));
    }

    public function test_unwrapping_rewinds_the_source_first(): void
    {
        $stream = Stream::fromString("UNH+1'UNT+1'");
        $stream->getSegment();

        static::assertSame(["UNH+1'UNT+1'"], $this->contents($stream->unwrap()));
    }

    public function test_yielded_partials_are_positioned_at_their_first_segment(): void
    {
        $stream = Stream::fromString("UNH+1'UNT+1'UNH+2'UNT+2'");

        $firstSegments = [];
        foreach ($stream->unwrap() as $partial) {
            $firstSegments[] = $partial->getSegment();
        }

        static::assertSame(['UNH+1', 'UNH+2'], $firstSegments);
    }

    public function test_unwrapping_an_empty_stream(): void
    {
        static::assertSame([], $this->contents(Stream::fromString('')->unwrap()));
    }

    /**
     * @param iterable<Stream> $streams
     * @return list<string>
     */
    private function contents(iterable $streams): array
    {
        $contents = [];

        foreach ($streams as $stream) {
            static::assertInstanceOf(Stream::class, $stream);
            $contents[] = $stream->toString();
        }

        return $contents;
    }
}
