<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Rff;

class RffTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'RFF';
        $code = 'ABC';
        $referenz = '123456789012345678901234567890123456789012345678901234567890123456789A';

        $seg = Rff::fromAttributes($code, $referenz);
        
        $this->assertEquals($code, $seg->code());
        $this->assertEquals($referenz, $seg->referenz());
    }
}
