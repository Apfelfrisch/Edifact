<?php

namespace Proengeno\Edifact\Test\Validation;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Test\Fixtures\Message;
use Proengeno\Edifact\Validation\MessageValidator;
use Proengeno\Edifact\Exceptions\ValidationException;

class MessageValidatorTest extends TestCase 
{
    /** @test */
    public function it_can_vaildate_a_message_without_reloops()
    {
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $validator->validate($edifactMessage);
    }

    /** @test */
    public function it_can_vaildate_a_message_with_1_nested_reloop()
    {
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $validator->validate($edifactMessage);
    }

    /** @test */
    public function it_can_vaildate_a_message_with_multiple_nested_reloops()
    {
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $validator->validate($edifactMessage);
    }

    /** @test */
    public function it_checks_the_maximal_reloops()
    {
        $validator = new MessageValidator;

        foreach(range(1, 6) as $i) {
            $loopedSegments[] = "LIN+1'DTM+137:201604221414:203'";
        }
        $edifactMessage = Message::fromString(
            "UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'BGM+380+9'LIN+1'DTM+137:201604221414:203'" . implode($loopedSegments) . "UNS+D'UNT+18+2'UNZ+4+6910995E'"
        );
        $this->expectException(ValidationException::class);
        $validator->validate($edifactMessage);
    }

    /** @test */
    public function it_finds_illegal_segments()
    {
        // $validationBlueprint = ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ]
        $illegallSegement = 'ILG';
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? '" . $illegallSegement . "'");
        $this->expectException(ValidationException::class);
        $edifactMessage->validate();
    }

    /** @test */
    public function it_finds_illage_content_from_legal_segment_over_blueprint_templates()
    {
        // $validationBlueprint = ['name' => 'BGM', 'templates' => ['docCode' => ['7', '380']] ]
        $illegallyBgm = 'BGM+ILG+9';
        $validator = new MessageValidator;
        
        $edifactMessage = Message::fromString("UNA:+.? 'UNH+1+MSG:D:11A:UN:5.1e'" . $illegallyBgm . "'LIN+1'DTM+137:201604221414:203'UNS+D'UNT+18+2'UNZ+4+6910995E'");
        $this->expectException(ValidationException::class);
        $validator->validate($edifactMessage);
    }
}
