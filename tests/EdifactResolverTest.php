<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactResolver;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Test\Fixtures\Message as MessageFixure;

class EdifactResolverTest extends TestCase
{
    private $ediResolver;

    public function setUp()
    {
        $configuration = $this->getConfiguration();
        $configuration->addImportAllocationRule(MessageFixure::class, [
            'UNH' => '/UNH\+(.*?)\+ORDERS\:/',
            'RFF' => '/RFF\+Z13\:17103/',
        ]);
        $this->ediResolver = new EdifactResolver($configuration);
    }

    /** @test */
    public function it_resolves_the_object_from_a_given_string()
    {

        $ediObject = $this->ediResolver->fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'");
        $this->assertInstanceOf(Message::class, $ediObject);

        $this->expectException(EdifactException::class);
        $ediObject = $this->ediResolver->fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17105'");
    }

    /** @test */
    public function it_resoves_the_object_from_a_given_file()
    {
        $ediObject = $this->ediResolver->fromFile(__DIR__ . "/data/edifact.txt");
        $this->assertInstanceOf(Message::class, $ediObject);
    }
}
