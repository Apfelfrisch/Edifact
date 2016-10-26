<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactBuilder;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Configuration;

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
    public function it_build_the_file_in_memory_if_only_path_is_given()
    {
        $path = '/tmp';

        $this->edifactBuilder->addBuilder('Message', Builder::class, 'from', '/tmp');
        $file = $this->edifactBuilder->build('Message', 'to')->get();

        $this->assertInstanceOf(Message::class, $file);
    }

    /** @test */
    public function it_build_the_file_in_current_path_if_only_a_filname_was_given()
    {
        $filename = 'test.csv';

        $this->edifactBuilder->addBuilder('Message', Builder::class, 'from');
        $this->edifactBuilder->build('Message', 'to', 'test.csv')->get();

        $this->assertFileExists($filename);
        @unlink($filename);
    }

    /** @test */
    public function it_build_the_file_in_given_path_if_path_and_filname_was_given()
    {
        $path = '/tmp';
        $filename = 'test.csv';

        $this->edifactBuilder->addBuilder('Message', Builder::class, 'from', '/tmp');
        $this->edifactBuilder->build('Message', 'to', 'test.csv')->get();

        $this->assertFileExists($path . '/' . $filename);
        @unlink($path . '/' . $filename);
    }

    /** @test */
    public function it_forwards_the_prebuild_configuration_to_builder_class()
    {
        $ownRef = 'OWN_REF';
        $configuration = new Configuration;
        $configuration->setUnbRefGenerator(function() use ($ownRef) {
            return $ownRef;
        });

        $edifactBuilder = new EdifactBuilder($configuration);
        $edifactBuilder->addBuilder('Message', Builder::class, 'from');

        $this->assertEquals($ownRef, $edifactBuilder->build('Message', 'to')->unbReference());
    }

    /** @test */
    public function it_throw_an_expetion_if_building_class_is_unknown()
    {
        $this->expectException(EdifactException::class);
        $this->edifactBuilder->build('Message', 'to');
    }
}
