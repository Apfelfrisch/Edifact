<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Qty;

class QtyTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'QTY';
        $qualifier = 'ABC';
        $amount = '1234567890123456789012345678901234A';
        $unitCode = '1234567A';

        $seg = Qty::fromAttributes($qualifier, $amount, $unitCode);
        
        $this->assertEquals($qualifier, $seg->qualifier());
        $this->assertEquals($amount, $seg->amount());
        $this->assertEquals($unitCode, $seg->unitCode());
    }
}
