<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Edifact;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\EdifactResolver;
use Proengeno\Edifact\Test\Fixtures\Builder;

class EdifactTest extends TestCase
{
    private $edifactBuilder;

    public function setUp(): void
    {
        $configuration = $this->getConfiguration();
        $configuration->addBuilder('Message', Builder::class, 'from');

        $this->edifact = new Edifact($configuration);
    }

    /** @test */
    public function it_instanciates()
    {
        $this->assertInstanceOf(Edifact::class, $this->edifact);
    }

    /** @test */
    public function it_resolves_the_given_builder_instace()
    {
        $this->assertInstanceOf(Builder::class, $this->edifact->build('Message', 'to'));
    }

    /** @test */
    public function it_resolves_the_object_from_a_given_string()
    {
        $ediObject = $this->edifact->resolveFromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'");
        $this->assertInstanceOf(Message::class, $ediObject);
    }

    /** @test */
    public function it_resoves_the_object_from_a_given_file()
    {
        $this->assertInstanceOf(Message::class, $this->edifact->resolveFromFile(__DIR__ . "/data/edifact.txt"));
    }

    /** @test */
    public function it_build_the_object_from_a_given_string()
    {
        $string = "UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'";
        $filename = 'test.txt';
        $ediObject = $this->edifact->buildFromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'", $filename);

        $this->assertEquals($string, file_get_contents($filename));

        unlink($filename);
    }
}
