<?php

use Mockery as m;
use Proengeno\Edifact\Validation\MessageValidator;

class MessageValidatorTest extends TestCase 
{
    /** @test */
    public function it_can_vaildate_an_ok_message()
    {
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $validator->validate($edifactMessage);
    }

    /** @test */
    public function it_finds_illegal_segments()
    {
        // $validationBlueprint = ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ]
        $illegallSegement = 'ILG';
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? '" . $illegallSegement . "'");
        $this->expectException(Exception::class);
        $edifactMessage->validate();
    }

    /** @test */
    public function it_finds_illage_content_from_legal_segment_over_blueprint_templates()
    {
        // $validationBlueprint = ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ]
        $illegallyBgm = 'BGM+ILG+9';
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'" . $illegallyBgm . "'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $this->expectException(Exception::class);
        $validator->validate($edifactMessage);
    }
}
