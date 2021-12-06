<?php

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Stream;
use Apfelfrisch\Edifact\Test\TestCase;

class StreamTest extends TestCase
{
    private string $tempname;

    private Stream $stream;

    public function setUp(): void
    {
        $this->tempname = tempnam(sys_get_temp_dir(), 'diac');
        $this->stream = new Stream($this->tempname, 'w+');
    }

    public function tearDown(): void
    {
        if ($this->tempname && file_exists($this->tempname)) {
            unlink($this->tempname);
        }
    }

    public function test_provide_segement_line(): void
    {
        $stream = new Stream(__DIR__ . '/data/edifact.txt');

        $string = [];
        while (! $stream->eof()) {
            $string[] = $stream->getSegment();
        }

        $this->assertCount(3, $string);
        $this->assertEquals('UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e', $string[0]);
        $this->assertEquals('RFF+Z13:17103', $string[1]);
    }

    public function test_parsing_escaped_message(): void
    {
        $message = "SEG+Up?'Verd?''";
        $this->stream->writeAndRewind($message);

        $string = [];
        while (! $this->stream->eof()) {
            $string[] = $this->stream->getSegment();
        }
        $this->assertEquals("SEG+Up'Verd'", $string[0]);
    }

    public function test_string_cast_provides_the_full_stream_content(): void
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertEquals($message, (string) $this->stream);
    }

    public function test_string_serialization_returns_empty_string_when_stream_is_not_readable(): void
    {
        $this->tempname = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tempname, 'FOO BAR');
        $stream = new Stream($this->tempname, 'w');

        $this->assertEquals('', $stream->__toString());
    }

    public function test_seek_and_tell(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $stream->seek(2);

        $this->assertEquals(2, $stream->tell());
    }

    public function test_stat(): void
    {
        $this->assertFalse(empty($this->stream->stat()));
    }

    public function test_eof(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $stream->seek(2);
        $this->assertFalse($stream->eof());

        $stream->seek(0, SEEK_END);
        $stream->getChar();
        $this->assertTrue($stream->eof());
    }

    public function test_stream_is_empty(): void
    {
        $stream = $this->stream;

        $this->assertTrue($stream->isEmpty());

        $stream->write('A');
        $position = $stream->tell();

        $this->assertFalse($stream->isEmpty());
        $this->assertSame($position, $stream->tell());
    }

    public function test_getting_char(): void
    {
        $string = "UNA:+.? 'UNB?'UNT'";
        $stream = Stream::fromString($string);

        $stream->seek(0);
        $i = 0;
        while (isset($string[$i])) {
            $this->assertEquals($stream->getChar(), $string[$i]);
            $i++;
        }
    }

    public function test_rewind_resets_to_start_of_stream(): void
    {
        $stream = Stream::fromString('FOO BAR');
        $this->assertTrue($stream->seek(2));
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }

    public function test_read_filter(): void
    {
        $stream = Stream::fromString('foo bar');

        $stream->addReadFilter('string.toupper');

        $this->assertEquals('FOO BAR', (string)$stream);
        $stream->rewind();
        $this->assertEquals('FOO BAR', $stream->getContents());
        $stream->rewind();
        $this->assertEquals('FOO BAR', $stream->getSegment());
        $stream->rewind();
        $this->assertEquals('FOO BAR', $stream->read(1024));
        $stream->rewind();
        $this->assertEquals('F', $stream->getChar());
    }

    public function test_write_filter(): void
    {
        $stream = new Stream('php://temp', 'w+');
        $stream->addWriteFilter('string.toupper', STREAM_FILTER_WRITE);
        $stream->write('foo bar');
        $stream->rewind();
        $this->assertEquals('FOO BAR', $stream->getContents());
    }

    public function test_using_write_filter_over_static_constructor(): void
    {
        $stream = Stream::fromString('foo bar', 'php://temp', ['string.toupper']);
        $this->assertEquals('FOO BAR', $stream->getContents());
    }
}

