<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Iterators;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Iterators\InMemoryIterator;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class InMemoryIteratorTest extends TestCase
{
    public function test_iterating_over_segments(): void
    {
        $iterator = InMemoryIterator::fromSegments(
            GenericSegment::fromAttributes('UNH', ['1']),
            GenericSegment::fromAttributes('UNT', ['2']),
        );

        $names = [];

        foreach ($iterator as $key => $segment) {
            $names[$key] = $segment->name();
        }

        static::assertSame([0 => 'UNH', 1 => 'UNT'], $names);
    }

    public function test_rewinding_the_iterator(): void
    {
        $iterator = InMemoryIterator::fromSegments(GenericSegment::fromAttributes('UNH', ['1']));

        $iterator->next();
        static::assertFalse($iterator->valid());

        $iterator->rewind();
        static::assertTrue($iterator->valid());
        static::assertSame(0, $iterator->key());
        static::assertSame('UNH', $iterator->current()->name());
    }

    public function test_provides_the_default_una_segment_unless_given(): void
    {
        static::assertEquals(UnaSegment::getDefault(), new InMemoryIterator()->getUnaSegment());

        $unaSegment = new UnaSegment('|', '#');
        static::assertSame($unaSegment, new InMemoryIterator([], $unaSegment)->getUnaSegment());
    }

    public function test_throws_when_no_segment_is_available(): void
    {
        $iterator = new InMemoryIterator();

        $this->expectException(InvalidEdifactContentException::class);
        $iterator->current();
    }

    public function test_building_a_message_from_segments_in_memory(): void
    {
        $message = new Message(
            InMemoryIterator::fromSegments(
                GenericSegment::fromAttributes('UNH', ['1'], ['ORDERS', 'D', '96A', 'UN']),
                GenericSegment::fromAttributes('UNT', ['2'], ['1']),
            ),
            new EdifactFormatter(UnaSegment::getDefault()),
        );

        static::assertSame("UNH+1+ORDERS:D:96A:UN'UNT+2+1'", $message->toString());
    }
}
