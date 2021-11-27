<?php

namespace Apfelfrisch\Edifact\Test\Message;

use Apfelfrisch\Edifact\Message;
use DateTime;
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Delimiter;
use Apfelfrisch\Edifact\Segments\Ajt;
use Apfelfrisch\Edifact\Segments\Seq;
use Apfelfrisch\Edifact\Segments\Una;
use Apfelfrisch\Edifact\Segments\Unb;
use Apfelfrisch\Edifact\Segments\Unh;
use Apfelfrisch\Edifact\Segments\Unt;
use Apfelfrisch\Edifact\Test\TestCase;

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
            Una::fromAttributes('|', '#', '.', '!', ' '),
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA|#.! '", $message);
        $this->assertEquals(new Delimiter('|', '#', '.', '!'), $message->getDelimiter());
        $this->assertSame(
            "UNA|#.! 'UNB#1|2#sender|500#receiver|400#210101|1201#referenz-no'UNZ#0#referenz-no'",
            $message->toString()
        );
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
        $builder = new Builder;
        $builder->writeSegments(
            Ajt::fromAttributes('COD'),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
    }

    /** @test */
    public function test_using_stream_filters()
    {
        $builder = new Builder;
        $builder->addStreamFilter('string.toupper');
        $builder->writeSegments(
            Ajt::fromAttributes('cod'),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
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

    /** @test */
    public function test_counting_messages()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
        );

        $this->assertStringEndsWith("UNZ+3+unb-ref'", (string)$builder->get());
    }

    /** @test */
    public function test_counting_unh_segments()
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
            Seq::fromAttributes('COD'),
            Seq::fromAttributes('COD'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
            Seq::fromAttributes('COD'),
            Seq::fromAttributes('COD'),
            Seq::fromAttributes('COD'),
            Seq::fromAttributes('COD'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co'),
        );

        $message = new Message($builder->get());

        $unts = $message->findAllSegments(Unt::class);

        $this->assertSame('4', $unts[0]->segCount());
        $this->assertSame('6', $unts[1]->segCount());
        $this->assertSame('2', $unts[2]->segCount());
    }
}
