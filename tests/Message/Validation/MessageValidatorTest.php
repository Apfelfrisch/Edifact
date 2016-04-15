<?php

use Mockery as m;
use Proengeno\Edifact\Validation\MessageValidator;

class MessageValidatorTest extends TestCase 
{
    /** @test */
    public function it()
    {
        $validator = new MessageValidator();
        $edifactMessage = MessageDummy::fromString("UNA:+.? 'UNH'BGM'UNS'UNT'UNZ'");

        $validator->validate($edifactMessage);
    }
}
