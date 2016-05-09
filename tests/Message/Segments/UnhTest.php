<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Unh;

class UnhTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'UNH';
        $referenz = '1234567890123A';
        $type ='12345A';
        $versionNumber = 'ABC';
        $releaseNumber = 'CBA';
        $organisation = 'AB';
        $organisationCode = '12345A';

        $seg = Unh::fromAttributes($referenz, $type, $versionNumber, $releaseNumber, $organisation, $organisationCode);
        
        $this->assertEquals($segName, $seg->name());
        $this->assertEquals($referenz, $seg->referenz());
        $this->assertEquals($type, $seg->type());
        $this->assertEquals($versionNumber, $seg->versionNumber());
        $this->assertEquals($releaseNumber, $seg->releaseNumber());
        $this->assertEquals($organisation, $seg->organisation());
        $this->assertEquals($organisationCode, $seg->organisationCode());
    }
}
