<?php

namespace Proengeno\Edifact\Test\Message;

use Mockery as m;
use Proengeno\Edifact\Configuration;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Interfaces\MessageInterface;
use Proengeno\Edifact\Message\Message as MessageProxy;
use Proengeno\Edifact\Test\Fixtures\Message as Message;
use Proengeno\Edifact\Interfaces\MessageValidatorInterface;

class AbstractMessageTest extends TestCase
{
    private $message;
    private $messageProxy;

    public function setUp()
    {
        $file = new EdifactFile(__DIR__ . '/../data/edifact.txt');
        $this->message = new Message($file, $this->getConfiguration());
        $this->messageProxy = new MessageProxy(new Message($file, $this->getConfiguration()));
    }

    /** @test */
    public function it_provides_the_adapter_name()
    {
        $this->assertEquals('Message', $this->messageProxy->getAdapterName());
    }

    /** @test */
    public function it_provides_the_delimter_from_the_root_class()
    {
        $this->assertEquals($this->message->getDelimiter(), $this->messageProxy->getDelimiter());
    }

    /** @test */
    public function it_provides_the_file_path_from_the_root_class()
    {
        $this->assertEquals($this->message->getFilepath(), $this->messageProxy->getFilepath());
    }

    /** @test */
    public function it_provides_the_current_segment_from_the_root_class()
    {
        $this->assertEquals($this->message->getCurrentSegment(), $this->messageProxy->getCurrentSegment());
    }

    /** @test */
    public function it_provides_the_next_segment_from_the_root_class()
    {
        $segment = $this->message->getNextSegment();
        $this->message->rewind();
        $segmentProxy = $this->messageProxy->getNextSegment();
        $this->assertEquals($segment, $segmentProxy);
    }

    /** @test */
    public function it_finds_segment_from_the_root_class()
    {
        $segment = $this->message->findNextSegment('UNH');
        $this->message->rewind();
        $segmentProxy = $this->messageProxy->findNextSegment('UNH');
        $this->assertEquals($segment, $segmentProxy);
    }

    /** @test */
    public function it_iterates_over_the_message_proxy()
    {
        $message = '';
        $messageProxy = '';

        foreach ($this->message as $segment) {
            $message .= (string)$segment;
        }
        foreach ($this->messageProxy as $segment) {
            $messageProxy .= (string)$segment;
        }

        $this->assertEquals($message, $messageProxy);
    }
}
