<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactBuilderTest extends TestCase
{
    private $edifactBuilder;

    public function setUp()
    {
        $this->edifactBuilder = new EdifactBuilder;
    }

    /** @test */
    public function it_instanciates()
    {
        $this->assertInstanceOf(EdifactBuilder::class, $this->edifactBuilder);
    }

    /** @test */
    public function it_resolves_the_given_builder_instace()
    {
        $this->edifactBuilder->addBuilder('Message', Builder::class, 'from');
        $this->edifactBuilder->build('Message', 'to');
    }

    /** @test */
    public function it_forwards_the_prebuild_configuration_to_builder_class()
    {
        $ownRef = 'OWN_REF';
        $this->edifactBuilder->addBuilder('Message', Builder::class, 'from');
        $this->edifactBuilder->addPrebuildConfig('unbReference', function() use ($ownRef) {
            return $ownRef;
        });
        $this->assertEquals($ownRef, $this->edifactBuilder->build('Message', 'to')->unbReference());
    }

    /** @test */
    public function it_throw_an_expetion_if_building_class_is_unknown()
    {
        $this->expectException(EdifactException::class);
        $this->edifactBuilder->build('Message', 'to');
    }
}
