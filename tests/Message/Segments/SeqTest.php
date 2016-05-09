<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Seq;

class SeqTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'SEQ';
        $code = 'ABC';

        $seg = Seq::fromAttributes($code);
        
        $this->assertEquals($code, $seg->code());
    }
}
