<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unz;

class UnzTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'UNZ';
        $counter = 3;
        $referenz ='S';

        $seg = Unz::fromAttributes($counter, $referenz);
        
        $this->assertEquals($counter, $seg->counter());
        $this->assertEquals($referenz, $seg->referenz());
    }
}
