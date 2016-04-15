<?php

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Bgm;

class BgmTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'BGM';
        $docCode = 'ABC';
        $docNumber = '12345678901234567890123456789012345';
        $messageCode = 'CBA';

        $seg = Bgm::fromAttributes($docCode, $docNumber, $messageCode);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($docCode, $seg->docCode());
        $this->assertEquals($docNumber, $seg->docNumber());
        $this->assertEquals($messageCode, $seg->messageCode());
    }

    /** @test */
    public function it_can_set_and_fetch_basic_informations_without_a_messagecode()
    {
        $segName = 'BGM';
        $docCode = 'ABC';
        $docNumber = '12345678901234567890123456789012345';

        $seg = Bgm::fromAttributes($docCode, $docNumber);
        
        $this->assertEquals(null, $seg->messageCode());
    }
}
