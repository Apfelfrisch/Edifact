<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactResolver;
use Proengeno\Edifact\Test\Fixtures\Message;

class EdifactResolverTest extends TestCase
{
    /** @test */
    public function it_resolves_the_object_from_a_given_string()
    {
        $ediResolver = new EdifactResolver;
        $ediResolver->addAllocationRule(Message::class, [
            'UNH' => '/UNH\+(.*?)\+ORDERS\:/',
            'RFF' => '/RFF\+Z13\:17103/',
        ]);

        $ediObject = $ediResolver->fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17103'");
        $this->assertInstanceOf(Message::class, $ediObject);
        $ediObject = $ediResolver->fromString("UNH+O160482A7C2+ORDERS:D:09B:UN:1.1e'RFF+Z13:17105'");
        $this->assertNull($ediObject);
    }

    /** @test */
    public function it_resoves_the_object_from_a_given_file()
    {
        $ediResolver = new EdifactResolver;
        $ediResolver->addAllocationRule(Message::class, [
            'UNH' => '/UNH\+(.*?)\+ORDERS\:/',
            'RFF' => '/RFF\+Z13\:17103/',
        ]);

        $ediObject = $ediResolver->fromFile(__DIR__ . "/data/edifact.txt");
        $this->assertInstanceOf(Message::class, $ediObject);
    }
}
