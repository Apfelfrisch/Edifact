<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\EdifactRegistrar;
use Proengeno\Edifact\Exceptions\ValidationException;

class EdifactRegistrarTest extends TestCase 
{
    public function tearDown()
    {
        EdifactRegistrar::addMessage('ZZZ', null);
        EdifactRegistrar::addSegement('ZZZ', null);
    }
    
    /** @test */
    public function it_can_regsiter_and_resolve_a_new_segment_class_path()
    {
        $pathToSegementClass = Segment::class;

        EdifactRegistrar::addSegement('ZZZ', $pathToSegementClass);

        $this->assertEquals($pathToSegementClass, EdifactRegistrar::getSegment('ZZZ'));
    }

    /** @test */
    public function it_can_regsiter_and_resolve_a_new_message_class_path()
    {
        $pathToMessageClass = Message::class;

        EdifactRegistrar::addMessage('ZZZ', $pathToMessageClass);

        $this->assertEquals($pathToMessageClass, EdifactRegistrar::getMessage('ZZZ'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_segment_is_not_registered()
    {
        $pathToSegementClass = Segment::class;
        
        $this->expectException(ValidationException::class);
        $this->assertEquals($pathToSegementClass, EdifactRegistrar::getSegment('ZZZ'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_message_is_not_registered()
    {
        $pathToSegementClass = Segment::class;
        
        $this->expectException(ValidationException::class);
        $this->assertEquals($pathToSegementClass, EdifactRegistrar::getMessage('ZZZ'));
    }
}
