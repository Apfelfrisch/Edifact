<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Cux;

class CuxTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'CCI';
        $type = 'ABC';
        $currency = 'EUR';
        $qualifier = '12A';

        $seg = Cux::fromAttributes($type, $currency, $qualifier);
        
        $this->assertEquals($type, $seg->type());
        $this->assertEquals($currency, $seg->currency());
        $this->assertEquals($qualifier, $seg->qualifier());
    }
}
