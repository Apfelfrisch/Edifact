<?php

namespace Proengeno\Edifact\Test\Message;

use DateTime;
use Proengeno\Edifact\Builder;
use Proengeno\Edifact\Delimiter;
use Proengeno\Edifact\Segments\Una;
use Proengeno\Edifact\Segments\Unb;
use Proengeno\Edifact\Segments\Unh;
use Proengeno\Edifact\Test\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function test_setting_default_delimiter()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA:+.? '", $message);
        $this->assertEquals(new Delimiter(), $message->getDelimiter());
    }

    /** @test */
    public function test_using_delimter_from_una()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Una::fromAttributes('|', '+', '.', '?', ' '),
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA|+.? '", $message);
        $this->assertEquals(new Delimiter('|'), $message->getDelimiter());
    }

    /** @test */
    public function test_auto_write_unz_segement()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $this->assertStringEndsWith("UNZ+0+referenz-no'", (string)$builder->get());
    }

    /** @test */
    public function test_writing_to_a_file()
    {
        $builder = new Builder($filename = tempnam('/tmp', 'edi-test'));
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $this->assertStringEndsWith("UNZ+0+referenz-no'", (string)$builder->get());

        unlink($filename);
    }

    /** @test */
    public function test_auto_write_unt_segement()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
        );

        $this->assertStringEndsWith("UNT+2+unh-ref'UNZ+1+unb-ref'", (string)$builder->get());
    }
}
