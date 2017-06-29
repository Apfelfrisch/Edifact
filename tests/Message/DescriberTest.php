<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Describer;

class DescriberTest extends TestCase
{
    private $description;

    public function setUp()
    {
        $this->description = Describer::build(__DIR__ . '/../data/message_description.php');
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     **/
    public function it_throw_an_exception_if_the_given_file_does_not_exists()
    {
        $description = Describer::build('/invald/path.txt');
    }

    /** @test **/
    public function it_checks_if_the_descritpion_has_the_given_key()
    {
        $this->assertTrue($this->description->has('name'));
    }

    /** @test **/
    public function it_checks_if_the_descritpion_has_the_given_nested_key()
    {
        $this->assertTrue($this->description->has('explanation.utilmd'));
    }

    /** @test **/
    public function it_return_the_requested_key()
    {
        $this->assertEquals('TestMessage', $this->description->get('name'));
    }

    /** @test **/
    public function it_return_the_requested_nested_key()
    {
        $this->assertEquals('Edifact Message', $this->description->get('explanation.utilmd'));
    }
}
