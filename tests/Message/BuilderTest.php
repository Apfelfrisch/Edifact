<?php

use Mockery as m;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class BuilderTest extends TestCase 
{
    private $builder;

    public function setUp()
    {
        $this->builder = new Builder(Message::class, 'from', 'to', 'wb+');
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(BuilderCore::class, $this->builder);
    }

    /** @test */
    public function it_can_return_the_edifact_file()
    {
        $this->assertInstanceOf(EdifactFile::class, $this->builder->get());
    }
}
