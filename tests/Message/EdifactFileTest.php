<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Test\TestCase;

class EdifactFileTest extends TestCase
{
    public $tmpnam;

    /**
     * @var Stream
     */
    protected $stream;

    public function setUp()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        $this->stream = new EdifactFile($this->tmpnam, 'w+');
    }

    public function tearDown()
    {
        if ($this->tmpnam && file_exists($this->tmpnam)) {
            unlink($this->tmpnam);
        }
    }

    public function testCanInstantiateWithStreamIdentifier()
    {
        $this->assertInstanceOf('Proengeno\Edifact\Message\EdifactFile', $this->stream);
    }

    public function testCanGetEdifactSegments()
    {
        $stream = new EdifactFile($filePath = __DIR__ . '/../data/edifact.txt');

        while (! $stream->eof()) {
            $string[] = $stream->getSegment();
        }

        $this->assertEquals('UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e', $string[0]);
        $this->assertEquals('RFF+Z13:17103', $string[1]);
    }

    public function testTerminateSegmentDelimiter()
    {
        $message = "UNB'NAD+Up?'Verd?''UNZ";
        $this->stream->writeAndRewind($message);

        while (! $this->stream->eof()) {
            $string[] = $this->stream->getSegment();
        }
        $this->assertEquals("NAD+Up'Verd'", $string[1]);
    }

    public function testToStringRetrievesFullContentsOfStream()
    {
        $message = 'foo bar';
        $this->stream->write($message);
        $this->assertEquals($message, (string) $this->stream);
    }

    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $this->tmpnam = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new EdifactFile($this->tmpnam, 'w');

        $this->assertEquals('', $stream->__toString());
    }

    public function testSeekAndTellCurrentPositionInResource()
    {
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new EdifactFile($this->tmpnam, 'r');
        $stream->seek(2);

        $this->assertEquals(2, $stream->tell());
    }

    public function testStat()
    {
        $this->assertTrue(is_array($this->stream->stat()));
    }

    public function testEofReportsFalseWhenNotAtEndOfStream()
    {
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new EdifactFile($this->tmpnam, 'r');
        $stream->seek(2);
        $this->assertFalse($stream->eof());
    }

    public function testEofReportsTrueWhenAtEndOfStream()
    {
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new EdifactFile($this->tmpnam, 'r');

        $stream->seek(0, SEEK_END);
        $stream->getChar();
        $this->assertTrue($stream->eof());
    }

    public function testGettingChar()
    {
        $string = "UNA:+.? 'UNB?'UNT'";
        file_put_contents($this->tmpnam, $string);
        $stream = new EdifactFile($this->tmpnam, 'r');

        $stream->seek(0);
        $i = 0;
        while (isset($string[$i])) {
            $this->assertEquals($stream->getChar(), $string[$i]);
            $i++;
        }
    }

    public function testRewindResetsToStartOfStream()
    {
        file_put_contents($this->tmpnam, 'FOO BAR');
        $stream = new EdifactFile($this->tmpnam, 'r+');
        $this->assertTrue($stream->seek(2));
        $stream->rewind();
        $this->assertEquals(0, $stream->tell());
    }
}

