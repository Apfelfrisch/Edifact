<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactFactory;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Message\Messages\Orders_17103;

class EdifactFactoryTest extends TestCase 
{
    private $stream;
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
    
    /** @test */
    public function it_resolves_the_classname_over_type_and_referenz()
    {
        $this->stream->writeAndRewind("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'");
        $edifactObject = EdifactFactory::fromStream($this->stream);

        $this->assertInstanceOf(Orders_17103::class, $edifactObject);
    }

    /** @test */
    public function it_throw_an_excpetion_if_the_message_is_unknown()
    {
        $messageType = 'UNKNW';
        $referenz = '11111';
        $this->stream->writeAndRewind("UNH+O160482A7C2+$messageType:D:09B:UN:1.1e'RFF+Z13:$referenz'");

        $this->expectException(EdifactException::class);

        $edifactObject = EdifactFactory::fromStream($this->stream);
    }

    /** @test */
    public function it_throw_an_excpetion_if_refenz_was_not_found()
    {
        $this->stream->writeAndRewind("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:'");

        $this->expectException(EdifactException::class);

        $edifactObject = EdifactFactory::fromStream($this->stream);
    }

    /** @test */
    public function it_throw_an_excpetion_if_type_was_not_found()
    {
        $this->stream->writeAndRewind("UNH+O160482A7C2+:D:09B:UN:1.1e'RFF+Z13:17103'");

        $this->expectException(EdifactException::class);

        $edifactObject = EdifactFactory::fromStream($this->stream);
    }
}
