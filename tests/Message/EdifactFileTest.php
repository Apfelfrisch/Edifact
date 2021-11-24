<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;

class EdifactFileTest extends TestCase
{
    private string $tempname;

    private EdifactFile $edifactFile;

    public function setUp(): void
    {
        $this->tempname = tempnam(sys_get_temp_dir(), 'diac');
        $this->edifactFile = new EdifactFile($this->tempname, 'w+');
    }

    public function tearDown(): void
    {
        if ($this->tempname && file_exists($this->tempname)) {
            unlink($this->tempname);
        }
    }

    public function testCanInstantiateWithStreamIdentifier()
    {
        $this->assertInstanceOf('Proengeno\Edifact\EdifactFile', $this->edifactFile);
    }

    public function testCanGetEdifactSegments()
    {
        $edifactFile = new EdifactFile(__DIR__ . '/../data/edifact.txt');

        while (! $edifactFile->eof()) {
            $string[] = $edifactFile->getSegment();
        }

        $this->assertEquals('UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e', $string[0]);
        $this->assertEquals('RFF+Z13:17103', $string[1]);
    }

    public function testTerminateSegmentDelimiter()
    {
        $message = "SEG+Up?'Verd?''";
        $this->edifactFile->writeAndRewind($message);

        while (! $this->edifactFile->eof()) {
            $string[] = $this->edifactFile->getSegment();
        }
        $this->assertEquals("SEG+Up'Verd'", $string[0]);
    }

    public function testToStringRetrievesFullContentsOfStream()
    {
        $message = 'foo bar';
        $this->edifactFile->write($message);
        $this->assertEquals($message, (string) $this->edifactFile);
    }

    public function testStringSerializationReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $this->tempname = tempnam(sys_get_temp_dir(), 'diac');
        file_put_contents($this->tempname, 'FOO BAR');
        $edifactFile = new EdifactFile($this->tempname, 'w');

        $this->assertEquals('', $edifactFile->__toString());
    }

    public function testSeekAndTellCurrentPositionInResource()
    {
        file_put_contents($this->tempname, 'FOO BAR');
        $edifactFile = new EdifactFile($this->tempname, 'r');
        $edifactFile->seek(2);

        $this->assertEquals(2, $edifactFile->tell());
    }

    public function testStat()
    {
        $this->assertTrue(is_array($this->edifactFile->stat()));
    }

    public function testEofReportsFalseWhenNotAtEndOfStream()
    {
        file_put_contents($this->tempname, 'FOO BAR');
        $edifactFile = new EdifactFile($this->tempname, 'r');
        $edifactFile->seek(2);
        $this->assertFalse($edifactFile->eof());
    }

    public function testEofReportsTrueWhenAtEndOfStream()
    {
        file_put_contents($this->tempname, 'FOO BAR');
        $edifactFile = new EdifactFile($this->tempname, 'r');

        $edifactFile->seek(0, SEEK_END);
        $edifactFile->getChar();
        $this->assertTrue($edifactFile->eof());
    }

    public function testGettingChar()
    {
        $string = "UNA:+.? 'UNB?'UNT'";
        file_put_contents($this->tempname, $string);
        $edifactFile = new EdifactFile($this->tempname, 'r');

        $edifactFile->seek(0);
        $i = 0;
        while (isset($string[$i])) {
            $this->assertEquals($edifactFile->getChar(), $string[$i]);
            $i++;
        }
    }

    public function testRewindResetsToStartOfStream()
    {
        file_put_contents($this->tempname, 'FOO BAR');
        $edifactFile = new EdifactFile($this->tempname, 'r+');
        $this->assertTrue($edifactFile->seek(2));
        $edifactFile->rewind();
        $this->assertEquals(0, $edifactFile->tell());
    }

    public function testReadFilter()
    {
        file_put_contents($this->tempname, 'foo bar');
        $edifactFile = new EdifactFile($this->tempname, 'r+');

        $edifactFile->addReadFilter('string.toupper');

        $this->assertEquals('FOO BAR', (string)$edifactFile);
        $edifactFile->rewind();
        $this->assertEquals('FOO BAR', $edifactFile->getContents());
        $edifactFile->rewind();
        $this->assertEquals('FOO BAR', $edifactFile->getSegment());
        $edifactFile->rewind();
        $this->assertEquals('FOO BAR', $edifactFile->read(1024));
        $edifactFile->rewind();
        $this->assertEquals('F', $edifactFile->getChar());
    }

    public function testWriteFilter()
    {
        $edifactFile = new EdifactFile('php://temp', 'w+');
        $edifactFile->addWriteFilter('string.toupper', STREAM_FILTER_WRITE);
        $edifactFile->write('foo bar');
        $edifactFile->rewind();
        $this->assertEquals('FOO BAR', $edifactFile->getContents());
    }

    public function testUsingWriteFilterOverStaticConstructor()
    {
        $edifactFile = EdifactFile::fromString('foo bar', 'php://temp', ['string.toupper']);
        $this->assertEquals('FOO BAR', $edifactFile->getContents());
    }
}

