<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Doc;

class DocTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'DOC';
        $code = '380';
        $number = '1234567890123456789012345678901234A';

        $seg = Doc::fromAttributes($code, $number);
        
        $this->assertEquals($code, $seg->code());
        $this->assertEquals($number, $seg->number());
    }
}
