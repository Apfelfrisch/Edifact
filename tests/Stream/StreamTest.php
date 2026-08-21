<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Stream;

use Apfelfrisch\Edifact\Exceptions\InvalidStreamException;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use Override;

final class StreamTest extends TestCase
{
    private string $tempname;

    private Stream $stream;

    #[Override]
    protected function setUp(): void
    {
        $tempname = tempnam(sys_get_temp_dir(), 'diac');
        self::assertIsString($tempname);

        $this->tempname = $tempname;
        $this->stream = new Stream($this->tempname, 'w+');
    }

    #[Override]
    protected function tearDown(): void
    {
        if (file_exists($this->tempname)) {
            unlink($this->tempname);
        }
    }

    public function test_provide_segement_line(): void
    {
        $stream = new Stream(__DIR__ . '/../data/edifact.txt');

        $string = [];

        while (!$stream->eof()) {
            $string[] = $stream->getSegment();
        }

        static::assertCount(3, $string);
        static::assertSame('UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e', $string[0]);
        static::assertSame('RFF+Z13:17103', $string[1]);
    }

    public function test_parsing_escaped_message(): void
    {
        $message = "SEG+FirstSegOne?'SecondSegOne?'LastSegOne'SegTwo'";
        $this->stream->writeAndRewind($message);

        $string = [];

        while (!$this->stream->eof()) {
            $string[] = $this->stream->getSegment();
        }

        static::assertSame("SEG+FirstSegOne'SecondSegOne'LastSegOne", $string[0]);
        static::assertSame('SegTwo', $string[1]);
    }

    public function test_string_cast_provides_the_full_stream_content(): void
    {
        $message = 'foo bar';
        $this->stream->write($message);
        static::assertSame($message, (string) $this->stream);
    }

    public function test_string_serialization_returns_empty_string_when_stream_is_not_readable(): void
    {
        file_put_contents($this->tempname, 'FOO BAR');
        $stream = new Stream($this->tempname, 'w');

        static::assertSame('', $stream->__toString());
    }

    public function test_seek_and_tell(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $stream->seek(2);

        static::assertSame(2, $stream->tell());
    }

    public function test_stat(): void
    {
        static::assertNotEmpty($this->stream->stat());
    }

    public function test_eof(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $stream->seek(2);
        static::assertFalse($stream->eof());

        $stream->seek(0, SEEK_END);
        $stream->getChar();
        static::assertTrue($stream->eof());
    }

    public function test_stream_is_empty(): void
    {
        $stream = $this->stream;

        static::assertTrue($stream->isEmpty());

        $stream->write('A');
        $stream->rewind();
        $position = $stream->tell();

        static::assertFalse($stream->isEmpty());
        static::assertSame($position, $stream->tell());
    }

    public function test_getting_char(): void
    {
        $string = "UNA:+.? 'UNB?'UNT'";
        $stream = Stream::fromString($string);

        $stream->seek(0);

        foreach (str_split($string) as $char) {
            static::assertSame($stream->getChar(), $char);
        }
    }

    public function test_rewind_resets_to_start_of_stream(): void
    {
        $stream = Stream::fromString('FOO BAR');
        static::assertTrue($stream->seek(2));
        $stream->rewind();
        static::assertSame(0, $stream->tell());
    }

    public function test_read_filter(): void
    {
        $stream = Stream::fromString('foo bar');

        $stream->addReadFilter('string.toupper');

        static::assertSame('FOO BAR', (string) $stream);
        $stream->rewind();
        static::assertSame('FOO BAR', $stream->getContents());
        $stream->rewind();
        static::assertSame('FOO BAR', $stream->getSegment());
        $stream->rewind();
        static::assertSame('FOO BAR', $stream->read(1024));
        $stream->rewind();
        static::assertSame('F', $stream->getChar());
    }

    public function test_write_filter(): void
    {
        $stream = new Stream('php://temp', 'w+');
        $stream->addWriteFilter('string.toupper', [STREAM_FILTER_WRITE]);
        $stream->write('foo bar');
        $stream->rewind();
        static::assertSame('FOO BAR', $stream->getContents());
    }

    public function test_using_write_filter_over_static_constructor(): void
    {
        $stream = Stream::fromString('foo bar', 'php://temp', ['string.toupper']);
        static::assertSame('FOO BAR', $stream->getContents());
    }

    public function test_reading_una_from_file(): void
    {
        foreach (['r', 'r+'] as $openMode) {
            $stream = new Stream(__DIR__ . '/../data/custom-una.txt', $openMode); // UNA|-,!_#'

            $unaSegment = $stream->getUnaSegment();

            static::assertSame('|', $unaSegment->componentSeparator());
            static::assertSame('-', $unaSegment->elementSeparator());
            static::assertSame(',', $unaSegment->decimalPoint());
            static::assertSame('!', $unaSegment->escapeCharacter());
            static::assertSame('_', $unaSegment->spaceCharacter());
            static::assertSame('#', $unaSegment->segmentTerminator());
        }
    }

    public function test_throws_when_the_file_cannot_be_opened(): void
    {
        $this->expectException(InvalidStreamException::class);
        new Stream('/path/does/not/exist/file.txt');
    }

    public function test_throws_when_adding_an_unknown_filter(): void
    {
        $this->expectException(InvalidStreamException::class);
        $this->stream->addReadFilter('unknown.filter');
    }

    public function test_a_segment_consisting_of_the_string_zero_is_parsed(): void
    {
        $stream = Stream::fromString("0'AAA'");

        static::assertSame('0', $stream->getSegment());
        static::assertSame('AAA', $stream->getSegment());
    }

    public function test_parsing_una_from_the_string_constructor(): void
    {
        $stream = Stream::fromString('UNA|#,!_"AAA#1"');

        static::assertSame('UNA|#,!_"', $stream->getUnaSegment()->toString());
    }

    public function test_write_only_stream_uses_the_given_una_segment(): void
    {
        $stream = new Stream($this->tempname, 'w', new UnaSegment('|', '#'));

        static::assertSame('|', $stream->getUnaSegment()->componentSeparator());
        static::assertSame('#', $stream->getUnaSegment()->elementSeparator());
    }
}
