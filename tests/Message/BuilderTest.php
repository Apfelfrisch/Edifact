<?php

namespace Proengeno\Edifact\Test\Message;

use Proengeno\Edifact\EdifactFile;
use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Test\Fixtures\Message;
use Proengeno\Edifact\Message\Builder as BuilderCore;

class BuilderTest extends TestCase 
{
    private $builder;
    private $file;

    public function setUp()
    {
        $this->builder = new Builder(Message::class, 'from', 'to', 'wb+');
        $this->file = $this->builder->getEdifactFile();
    }

    public function tearDown()
    {
        parent::tearDown();
        $filepath = $this->file->getRealPath();
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /** @test */
    public function it_instanciates_with_file_and_validator()
    {
        $this->assertInstanceOf(BuilderCore::class, $this->builder);
    }

    /** @test */
    public function it_deletes_the_file_if_the_building_was_interrupted()
    {
        $filepath = $this->file->getRealPath();
        $this->assertFileExists($filepath);
        unset($this->builder);
        $this->assertFalse(file_exists($filepath));
    }

    /** @test */
    public function it_can_instanciate_the_edifact_message()
    {
        $this->assertInstanceOf(Message::class, $this->builder->get());
    }

    /** @test */
    public function it_provides_the_unb_ref()
    {
        $this->assertEquals('M_unique_id', $this->builder->unbReference());
    }

    /** @test */
    public function it_dont_sets_the_header_an_footer_when_no_message_was_added()
    {
        $this->assertEmpty((string)$this->builder->get());
    }

    /** @test */
    public function it_sets_the_header_an_footer_from_the_edifact_message()
    {
        $expectedMessage = "UNA:+.? 'UNB+UNOC:3+from:500+to:500+160510:0143+M_unique_id+VL'UNZ+1+M_unique_id'";

        $message = $this->builder->addMessage([])->get();

        $this->assertStringStartsWith("UNA:+.? 'UNB+UNOC:3+from:500+to:500", (string)$message);
        $this->assertStringEndsWith("UNZ+1+M_unique_id'", (string)$message);
    }

    /** @test */
    public function it_counts_the_given_messages_and_writes_the_right_unz_segment()
    {
        $messageCount = 2;

        foreach (range(1, $messageCount) as $i ) {
            $this->builder->addMessage(['']);
        }

        $message = $this->builder->get();

        $this->assertStringStartsWith("UNA:+.? 'UNB+UNOC:3+from:500+to:500", (string)$message);
        $this->assertStringEndsWith("UNZ+" . $messageCount . "+M_unique_id'", (string)$message);
    }
}

namespace Proengeno\Edifact\Message;

function uniqid($prefix = null) {
    return $prefix . '_unique_id';
}
