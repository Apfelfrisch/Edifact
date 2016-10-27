<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Edifact;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\EdifactResolver;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Test\Fixtures\Message as MessageFixure;

class EdifactTest extends TestCase
{
    private $edifactBuilder;

    public function setUp()
    {
        $configuration = $this->getConfiguration();
        $configuration->addImportAllocationRule(MessageFixure::class, ['UNH' => '/ORDERS/']);

        $builder = new EdifactBuilder;
        $builder->addBuilder('Message', Builder::class, 'from');

        $ediResolver = new EdifactResolver($configuration);

        $this->edifact = new Edifact($builder, $ediResolver);
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
}
