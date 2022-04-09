<?php

namespace Apfelfrisch\Edifact\Test\Message;

use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\Fixtures\Moa;
use Apfelfrisch\Edifact\Test\TestCase;

class BuilderTest extends TestCase
{
    /** @test */
    public function test_setting_default_una(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2', '3', '4', '5']),
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA:+.? '", $message->toString());
        $this->assertEquals(new UnaSegment(), $message->getUnaSegment());
    }

    /** @test */
    public function test_using_custom_una(): void
    {
        $builder = new Builder(new UnaSegment('|', '#', ',', '!', '_'));
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['referenz no']),
            Moa::fromAttributes('110', 1.2),
        );

        $message = $builder->get();

        $this->assertStringStartsWith("UNA|#,!_'", $message->toString());
        $this->assertEquals(new UnaSegment('|', '#', ',', '!', '_'), $message->getUnaSegment());
        $this->assertSame(
            "UNA|#,!_'UNB#1|2#sender|500#receiver|400#210101|1201#referenz_no'MOA#110|1,20'UNZ#0#referenz_no'",
            $message->toString()
        );
    }

    /** @test */
    public function test_provide_message_is_empty(): void
    {
        $builder = new Builder(new UnaSegment('|', '#', ',', '!', '_'));

        $this->assertTrue($builder->messageIsEmpty());

        $builder->writeSegments(
            GenericSegment::fromAttributes('FTX', ['Foo?', 'Bar']),
        );

        $this->assertFalse($builder->messageIsEmpty());
    }

    /** @test */
    public function test_terminate_strings(): void
    {
        $builder = new Builder();
        $builder->writeSegments(
            GenericSegment::fromAttributes('FTX', ['Foo?', 'Bar']),
        );

        $message = $builder->get();

        $this->assertSame("UNA:+.? 'FTX+Foo??:Bar'", $message->toString());
    }

    /** @test */
    public function test_auto_write_unz_segement(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['referenz-no']),
        );

        $this->assertStringEndsWith("UNZ+0+referenz-no'", (string)$builder->get());
    }

    /** @test */
    public function test_writing_to_a_file(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('AJT', ['COD']),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
    }

    /** @test */
    public function test_using_stream_filters(): void
    {
        $builder = new Builder;
        $builder->addStreamFilter('string.toupper');
        $builder->writeSegments(
            GenericSegment::fromAttributes('AJT', ['cod']),
        );

        $this->assertSame("UNA:+.? 'AJT+COD'", (string)$builder->get());
    }

    /** @test */
    public function test_auto_write_unt_segement(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref-two'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
        );

        $this->assertSame("UNA:+.? 'UNH+unh-ref+type:v-no:r-no:o-no:o-co'UNT+2+unh-ref'UNH+unh-ref-two+type:v-no:r-no:o-no:o-co'UNT+2+unh-ref-two'", (string)$builder->get());
    }

    /** @test */
    public function test_counting_messages(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['unb-ref']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
        );

        $this->assertStringEndsWith("UNZ+3+unb-ref'", (string)$builder->get());
    }

    /** @test */
    public function test_counting_unh_segments(): void
    {
        $builder = new Builder;
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['unb-ref']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('SEQ', ['COD']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
        );

        $message = new Message($builder->get(), (new SegmentFactory)->addFallback(GenericSegment::class));

        $unts = $message->filterAllSegments(GenericSegment::class, fn(GenericSegment $seg) => $seg->name() === 'UNT');

        $this->assertSame('4', $unts[0]->getValue('1', '0'));
        $this->assertSame('6', $unts[1]->getValue('1', '0'));
        $this->assertSame('2', $unts[2]->getValue('1', '0'));
    }

    /** @test */
    public function test_write_direcly_into_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test-edi-file');

        $builder = new Builder(filepath: $tempFile);
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['unb-ref']),
        );
        $builder->get();

        // File stays because the Message was Build, even if the Builder is unlinked
        unset($builder);

        $this->assertSame(
            "UNA:+.? 'UNB+1:2+sender:500+receiver:400+210101:1201+unb-ref'UNZ+0+unb-ref'",
            file_get_contents($tempFile)
        );

        unlink($tempFile);
    }

    /** @test */
    public function test_remove_edifact_file_when_build_was_not_finished(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test-edi-file');

        $builder = new Builder(filepath: $tempFile);
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNB', ['1', '2'], ['sender', '500'], ['receiver', '400'], ['210101', '1201'], ['unb-ref']),
        );

        $this->assertTrue(file_exists($tempFile));

        unset($builder);

        $this->assertFalse(file_exists($tempFile));
    }
}
