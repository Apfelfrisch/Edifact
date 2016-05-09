<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactRegistrar;
use Proengeno\Edifact\Exceptions\EdifactException;

class EdifactRegistrarTest extends TestCase 
{
    /** @test */
    public function it_can_regsiter_resolve_a_new_segment_class_path()
    {
        $pathToSegementClass = Segment::class;

        EdifactRegistrar::addSegement('ZZZ', $pathToSegementClass);

        $this->assertEquals($pathToSegementClass, EdifactRegistrar::getSegment('ZZZ'));

        EdifactRegistrar::addSegement('ZZZ', null);
    }

    /** @test */
    public function it_can_regsiter_resolve_a_new_message_class_path()
    {
        $pathToMessageClass = Message::class;

        EdifactRegistrar::addMessage('UTILMD', $pathToMessageClass);

        $this->assertEquals($pathToMessageClass, EdifactRegistrar::getMessage('UTILMD'));

        EdifactRegistrar::addMessage('ZZZ', null);
    }

    /** @test */
    public function it_throws_an_exception_if_the_class_is_not_registered()
    {
        $pathToSegementClass = Segment::class;
        
        $this->expectException(EdifactException::class);
        $this->assertEquals($pathToSegementClass, EdifactRegistrar::getSegment('ZZZ'));
    }
}
