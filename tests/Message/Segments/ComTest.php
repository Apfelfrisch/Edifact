<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Com;

class ComTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'COM';
        $id = 'ABC92834z928hasuohd9adza0d8sza08s9dza0s8dza0sdza09sdz0aszd08az0da8zs0d8za08d0za8';
        $type = 'ABC';

        $seg = Com::fromAttributes($id, $type);
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($id, $seg->id());
        $this->assertEquals($type, $seg->type());
    }
}
