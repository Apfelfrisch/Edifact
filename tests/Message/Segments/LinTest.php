<?php

namespace Proengeno\Edifact\Test\Message\Segments;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\Segments\Lin;

class LinTest extends TestCase 
{
    /** @test */
    public function it_can_set_and_fetch_basic_informations()
    {
        $segName = 'CCI';
        $number = '123456';
        $articleNumber = '12345678901234567890123456789012345';
        $articleCode = 'ABC';

        $seg = Lin::fromAttributes($number, $articleNumber, $articleCode);
        
        $this->assertEquals($number, $seg->number());
        $this->assertEquals($articleNumber, $seg->articleNumber());
        $this->assertEquals($articleCode, $seg->articleCode());
    }
}
