<?php

namespace Apfelfrisch\Edifact\Test\Message;

use Apfelfrisch\Edifact\Message;
use DateTime;
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\UnaSegment;
use Apfelfrisch\Edifact\Segments\Ajt;
use Apfelfrisch\Edifact\Segments\Seq;
use Apfelfrisch\Edifact\Segments\Unb;
use Apfelfrisch\Edifact\Segments\Unh;
use Apfelfrisch\Edifact\Segments\Unt;
use Apfelfrisch\Edifact\Test\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function test_setting_default_una(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA:+.? '", $message->toString());
        $this->assertEquals(new UnaSegment(), $message->getUnaSegment());
    }

    /** @test */
    public function test_using_custom_una(): void
    {
        $builder = new Builder(new UnaSegment('|', '#', '.', '!', ' '));
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA|#.! '", $message->toString());
        $this->assertEquals(new UnaSegment('|', '#', '.', '!'), $message->getUnaSegment());
        $this->assertSame(
            "UNA|#.! 'UNB#1|2#sender|500#receiver|400#210101|1201#referenz-no'UNZ#0#referenz-no'",
            $message->toString()
        );
    }

    /** @test */
    public function test_auto_write_unz_segement(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'referenz-no')
        );

        $this->assertStringEndsWith("UNZ+0+referenz-no'", (string)$builder->get());
    }

    /** @test */
    public function test_writing_to_a_file(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            Ajt::fromAttributes('COD'),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
    }

    /** @test */
    public function test_using_stream_filters(): void
    {
        $builder = new Builder;
        $builder->addStreamFilter('string.toupper');
        $builder->writeSegments(
            Ajt::fromAttributes('cod'),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
    }

    /** @test */
    public function test_auto_write_unt_segement(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            Unb::fromAttributes('1', '2', 'sender', '500', 'receiver', '400', new DateTime('2021-01-01 12:01:01'), 'unb-ref'),
            Unh::fromAttributes('unh-ref', 'type', 'v-no', 'r-no', 'o-no', 'o-co')
        );

        $this->assertStringEndsWith("UNT+2+unh-ref'UNZ+1+unb-ref'", (string)$builder->get());
    }

    /** @test */
    public function test_counting_messages(): void
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
    public function test_counting_unh_segments(): void
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

        $unts = $message->filterAllSegments(Unt::class);

        $this->assertSame('4', $unts[0]->segCount());
        $this->assertSame('6', $unts[1]->segCount());
        $this->assertSame('2', $unts[2]->segCount());
    }
}
