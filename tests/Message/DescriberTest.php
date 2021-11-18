<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Describer;

class DescriberTest extends TestCase
{
    private $description;

    public function setUp(): void
    {
        $this->description = Describer::build(__DIR__ . '/../data/message_description.php');
        Describer::clean();
    }

    /** @test **/
    public function it_instanciates_only_one_object_per_description()
    {
        $description1 = Describer::build(__DIR__ . '/../data/message_description.php');
        $description2 = Describer::build(__DIR__ . '/../data/message_description.php');

        $this->assertSame($description1, $description2);
    }

    public function it_throw_an_exception_if_the_given_file_does_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);

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
    public function it_returns_the_given_default_if_no_key_was_found()
    {
        $default = 'Key not found';
        $descriptionWithDefault = Describer::buildWithDefaultDescription(__DIR__ . '/../data/message_description.php', $default);

        $this->assertEquals($default, $this->description->get('unknow-key', $default));
        $this->assertEquals($default, $descriptionWithDefault->get('unknow-key'));
    }

    /** @test **/
    public function it_return_the_requested_nested_key()
    {
        $this->assertEquals('Edifact Message', $this->description->get('explanation.utilmd'));
    }
}
