<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Ide;

class IdeTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'CCI';
        $qualifier = 'ABC';
        $idNumber = '12345678901234567890123456789012345';

        $seg = Ide::fromAttributes($qualifier, $idNumber);
        
        $this->assertEquals($qualifier, $seg->qualifier());
        $this->assertEquals($idNumber, $seg->idNumber());
    }
}
