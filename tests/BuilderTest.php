<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test;

use Apfelfrisch\Edifact\Builder;
use Apfelfrisch\Edifact\Exceptions\BuildException;
use Apfelfrisch\Edifact\Exceptions\InvalidStreamException;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\StreamMessageFactory;
use Apfelfrisch\Edifact\Test\Fixtures\Moa;

final class BuilderTest extends TestCase
{
    public function test_setting_default_una(): void
    {
        $builder = new Builder();
        $builder->writeSegments(GenericSegment::fromAttributes('UNB', ['1', '2', '3', '4', '5']));

        $message = $builder->get();

        static::assertStringStartsWith("UNA:+.? '", $message->toString());
        static::assertEquals(new UnaSegment(), $message->getUnaSegment());
    }

    public function test_using_custom_una(): void
    {
        $builder = new Builder(new UnaSegment('|', '#', ',', '!', '_'));
        $builder->writeSegments(
            GenericSegment::fromAttributes(
                'UNB',
                ['1', '2'],
                ['sender', '500'],
                ['receiver', '400'],
                ['210101', '1201'],
                ['referenz no'],
            ),
            Moa::fromAttributes('110', 1.2),
        );

        $message = $builder->get();

        static::assertStringStartsWith("UNA|#,!_'", $message->toString());
        static::assertEquals(new UnaSegment('|', '#', ',', '!', '_'), $message->getUnaSegment());
        static::assertSame(
            "UNA|#,!_'UNB#1|2#sender|500#receiver|400#210101|1201#referenz_no'MOA#110|1,20'UNZ#0#referenz_no'",
            $message->toString(),
        );
    }

    public function test_provide_message_is_empty(): void
    {
        $builder = new Builder(new UnaSegment('|', '#', ',', '!', '_'));

        static::assertTrue($builder->messageIsEmpty());

        $builder->writeSegments(GenericSegment::fromAttributes('FTX', ['Foo?', 'Bar']));

        static::assertFalse($builder->messageIsEmpty());
    }

    public function test_terminate_strings(): void
    {
        $builder = new Builder();
        $builder->writeSegments(GenericSegment::fromAttributes('FTX', ['Foo?', 'Bar']));

        $message = $builder->get();

        static::assertSame("UNA:+.? 'FTX+Foo??:Bar'", $message->toString());
    }

    public function test_auto_write_unz_segement(): void
    {
        $builder = new Builder();
        $builder->writeSegments(GenericSegment::fromAttributes(
            'UNB',
            ['1', '2'],
            ['sender', '500'],
            ['receiver', '400'],
            ['210101', '1201'],
            ['referenz-no'],
        ));

        static::assertStringEndsWith("UNZ+0+referenz-no'", (string) $builder->get());
    }

    public function test_writing_to_a_file(): void
    {
        $builder = new Builder();
        $builder->writeSegments(GenericSegment::fromAttributes('AJT', ['COD']));

        static::assertSame("UNA:+.? 'AJT+COD'", (string) $builder->get());
    }

    public function test_using_stream_filters(): void
    {
        $builder = new Builder();
        $builder->addStreamFilter('string.toupper');
        $builder->writeSegments(GenericSegment::fromAttributes('AJT', ['cod']));

        static::assertSame("UNA:+.? 'AJT+COD'", (string) $builder->get());
    }

    public function test_auto_write_unt_segement(): void
    {
        $builder = new Builder();
        $builder->writeSegments(
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref-two'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
        );

        static::assertSame(
            "UNA:+.? 'UNH+unh-ref+type:v-no:r-no:o-no:o-co'UNT+2+unh-ref'UNH+unh-ref-two+type:v-no:r-no:o-no:o-co'UNT+2+unh-ref-two'",
            (string) $builder->get(),
        );
    }

    public function test_counting_messages(): void
    {
        $builder = new Builder();
        $builder->writeSegments(
            GenericSegment::fromAttributes(
                'UNB',
                ['1', '2'],
                ['sender', '500'],
                ['receiver', '400'],
                ['210101', '1201'],
                ['unb-ref'],
            ),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
            GenericSegment::fromAttributes('UNH', ['unh-ref'], ['type', 'v-no', 'r-no', 'o-no', 'o-co']),
        );

        static::assertStringEndsWith("UNZ+3+unb-ref'", (string) $builder->get());
    }

    public function test_write_direcly_into_file(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test-edi-file');
        static::assertIsString($tempFile);

        $builder = new Builder(filepath: $tempFile);
        $builder->writeSegments(GenericSegment::fromAttributes(
            'UNB',
            ['1', '2'],
            ['sender', '500'],
            ['receiver', '400'],
            ['210101', '1201'],
            ['unb-ref'],
        ));
        $builder->get();

        // The file stays because the message was built, even if the Builder is unlinked
        unset($builder);

        static::assertSame(
            "UNA:+.? 'UNB+1:2+sender:500+receiver:400+210101:1201+unb-ref'UNZ+0+unb-ref'",
            file_get_contents($tempFile),
        );

        unlink($tempFile);
    }

    public function test_remove_edifact_file_when_build_was_not_finished(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test-edi-file');
        static::assertIsString($tempFile);

        $builder = new Builder(filepath: $tempFile);
        $builder->writeSegments(GenericSegment::fromAttributes(
            'UNB',
            ['1', '2'],
            ['sender', '500'],
            ['receiver', '400'],
            ['210101', '1201'],
            ['unb-ref'],
        ));

        static::assertFileExists($tempFile);

        unset($builder);

        static::assertFileDoesNotExist($tempFile);
    }

    public function test_throw_exception_when_add_a_segement_to_a_finalized_build(): void
    {
        $builder = new Builder();
        $builder->writeSegments(GenericSegment::fromAttributes(
            'UNB',
            ['1', '2'],
            ['sender', '500'],
            ['receiver', '400'],
            ['210101', '1201'],
            ['referenz-no'],
        ));
        $builder->get();

        $this->expectException(BuildException::class);
        $builder->writeSegments(GenericSegment::fromAttributes(
            'UNB',
            ['1', '2'],
            ['sender', '500'],
            ['receiver', '400'],
            ['210101', '1201'],
            ['referenz-no'],
        ));
    }

    public function test_counting_written_messages(): void
    {
        $builder = new Builder();

        static::assertSame(0, $builder->getMessageCount());

        $builder->writeSegments(
            GenericSegment::fromAttributes('UNH', ['1']),
            GenericSegment::fromAttributes('SEQ', ['1']),
            GenericSegment::fromAttributes('UNH', ['2']),
        );

        static::assertSame(2, $builder->getMessageCount());
    }

    public function test_an_empty_build_provides_an_empty_stream(): void
    {
        $builder = new Builder();

        static::assertSame('', (string) $builder->get());
        static::assertSame(0, $builder->getMessageCount());
    }

    public function test_throws_when_adding_an_unknown_stream_filter(): void
    {
        $this->expectException(InvalidStreamException::class);
        new Builder()->addStreamFilter('unknown.filter');
    }

    public function test_counting_unh_segments(): void
    {
        $builder = new Builder();
        $builder->writeSegments(
            GenericSegment::fromAttributes(
                'UNB',
                ['1', '2'],
                ['sender', '500'],
                ['receiver', '400'],
                ['210101', '1201'],
                ['unb-ref'],
            ),
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

        $message = new StreamMessageFactory()
            ->addFallback(GenericSegment::class)
            ->fromString((string) $builder->get());

        $unts = $message->filterAllSegments(
            GenericSegment::class,
            static fn(GenericSegment $seg): bool => $seg->name() === 'UNT',
        );

        // Every UNT counts all segments of its message, including itself and the UNH
        static::assertCount(3, $unts);
        static::assertSame('4', $unts[0]->getValue('1', '0'));
        static::assertSame('6', $unts[1]->getValue('1', '0'));
        static::assertSame('2', $unts[2]->getValue('1', '0'));
    }
}
