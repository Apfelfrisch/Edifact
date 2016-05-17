<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Moa;

class MoaTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'MOA';
        $qualifier = '380';
        $amount = '1000';

        $seg = Moa::fromAttributes($qualifier, $amount);
        
        $this->assertEquals($qualifier, $seg->qualifier());
        $this->assertEquals($amount, $seg->amount());
    }
}
