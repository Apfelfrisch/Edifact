<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Segment;

use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\SegmentCounter;
use Apfelfrisch\Edifact\Test\TestCase;

final class SegmentCounterTest extends TestCase
{
    public function test_initial_counts_are_zero(): void
    {
        $counter = new SegmentCounter();

        static::assertSame(0, $counter->messageCount());
        static::assertSame(0, $counter->unhCount());
    }

    public function test_unb_segments_are_not_counted(): void
    {
        $counter = new SegmentCounter();

        $counter->count(GenericSegment::fromAttributes('UNB', ['1']));

        static::assertSame(0, $counter->messageCount());
        static::assertSame(0, $counter->unhCount());
    }

    public function test_unh_count_stays_zero_until_the_first_message_starts(): void
    {
        $counter = new SegmentCounter();

        $counter->count(GenericSegment::fromAttributes('SEQ', ['1']));

        static::assertSame(0, $counter->messageCount());
        static::assertSame(0, $counter->unhCount());
    }

    public function test_unh_starts_a_new_message(): void
    {
        $counter = new SegmentCounter();

        $counter->count(GenericSegment::fromAttributes('UNH', ['1']));

        static::assertSame(1, $counter->messageCount());
        static::assertSame(1, $counter->unhCount());

        $counter->count(GenericSegment::fromAttributes('SEQ', ['1']));
        $counter->count(GenericSegment::fromAttributes('SEQ', ['2']));

        static::assertSame(1, $counter->messageCount());
        static::assertSame(3, $counter->unhCount());
    }

    public function test_a_second_unh_resets_the_segment_count(): void
    {
        $counter = new SegmentCounter();

        $counter->count(GenericSegment::fromAttributes('UNH', ['1']));
        $counter->count(GenericSegment::fromAttributes('SEQ', ['1']));
        $counter->count(GenericSegment::fromAttributes('UNH', ['2']));

        static::assertSame(2, $counter->messageCount());
        static::assertSame(1, $counter->unhCount());
    }
}
