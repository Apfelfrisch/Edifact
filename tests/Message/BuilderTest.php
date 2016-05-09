<?php

namespace Proengeno\Edifact\Test\Message;

use Mockery as m;
use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Test\Fixtures\Message;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class BuilderTest extends TestCase 
{
    private $builder;

    public function setUp()
    {
        $this->builder = new Builder(Message::class, 'from', 'to', 'VL', 'wb+');
    }

    public function tearDown()
    {
        $filepath = $this->builder->get()->getFilepath();
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(BuilderCore::class, $this->builder);
    }

    /** @test */
    public function it_can_instanciate_the_edifact_message()
    {
        $this->assertInstanceOf(Message::class, $this->builder->get());
    }

    /** @test */
    public function it_provides_the_unb_ref()
    {
        $this->assertEquals('M_unique_id', $this->builder->unbReference());
    }

    /** @test */
    public function it_sets_the_header_an_footer_from_the_edifact_message()
    {
        //die(var_dump((string)$this->builder->get()));
    }
}

namespace Proengeno\Edifact\Message;

function uniqid($prefix = null) {
    return $prefix . '_unique_id';
}
