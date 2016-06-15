<?php

namespace Proengeno\Edifact\Test\Templates;

use Proengeno\Edifact\Test\TestCase;
use Proengeno\Edifact\Message\Message;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Message\EdifactFile;
use Proengeno\Edifact\Test\Fixtures\Builder;
use Proengeno\Edifact\Templates\AbstractBuilder;
use Proengeno\Edifact\Exceptions\EdifactException;
use Proengeno\Edifact\Exceptions\ValidationException;

class AbstractBuilderTest extends TestCase 
{
    private $builder;
    private $file;

    public function setUp()
    {
        $this->builder = new Builder('from', 'to');
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
        $this->assertInstanceOf(AbstractBuilder::class, $this->builder);
    }

    /** @test */
    public function it_deletes_the_file_if_the_building_was_interrupted()
    {
        $builder = new Builder('from', 'to', tempnam(sys_get_temp_dir(), 'edifFile'));
        $file = $builder->getEdifactFile();
        $filepath = $file->getRealPath();
        $this->assertFileExists($filepath);
        // Because the Closure in the Constructor, we cant simple unset the Class
        // and have to call teh desstructor manually (The destructor is called anyway, but not at this time)
        $builder->__destruct();
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
        $this->assertEquals('unique_id', $this->builder->unbReference());
    }

    /** @test */
    public function it_provides_a_configurable_pre_build_configuration()
    {
        $ownRef = 'OWN_REF';
        $this->builder->addPrebuildConfig('unbReference', $ownRef);
        $this->assertEquals($ownRef, $this->builder->unbReference());
    }

    /** @test */
    public function it_provides_a_configurable_pre_build_configuration_over_closures()
    {
        $ownRef = 'OWN_REF';
        $this->builder->addPrebuildConfig('unbReference', function() use ($ownRef) {
            return $ownRef;
        });
        $this->assertEquals($ownRef, $this->builder->unbReference());
    }

    /** @test */
    public function it_builds_with_the_configurable_delimiter()
    {
        $this->builder->addPrebuildConfig('delimiter', function() {
            return new Delimiter;
        });
    }

    /** @test */
    public function it_does_not_set_the_header_an_footer_when_no_message_was_added()
    {
        $this->assertEmpty((string)$this->builder->get());
    }

    /** @test */
    public function it_sets_the_header_an_footer_from_the_edifact_message()
    {
        $expectedMessage = "UNA:+.? 'UNB+UNOC:3+from:500+to:500+160510:0143+unique_id+VL'UNZ+1+unique_id'";

        $this->builder->addMessage([]);
        $message = $this->builder->get();
        $this->assertStringStartsWith("UNA:+.? 'UNB+UNOC:3+from:500+to:500", (string)$message);
        $this->assertStringEndsWith("UNZ+1+unique_id'", (string)$message);
    }
    
    /** @test */
    public function it_runs_validation_before_it_provides_the_message()
    {
        $this->builder->addMessage([]);
        $this->expectException(ValidationException::class);
        $this->builder->getOrFail();
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
        $this->assertStringEndsWith("UNZ+" . $messageCount . "+unique_id'", (string)$message);
    }

    /** @test */
    public function it_throws_an_expection_if_pre_build_config_was_addet_while_building_process_has_begun()
    {
        $ownRef = 'OWN_REF';
        $this->builder->unbReference();
        $this->expectException(EdifactException::class);
        $this->builder->addPrebuildConfig('unbReference', function() use ($ownRef) {
            return $ownRef;
        });
    }
}

namespace Proengeno\Edifact\Templates;

function uniqid($prefix = null) {
    return $prefix . 'unique_id';
}
