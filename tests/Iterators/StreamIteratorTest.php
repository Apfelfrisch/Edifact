<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Iterators;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Iterators\Stream\StreamIterator;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Test\TestCase;

final class StreamIteratorTest extends TestCase
{
    public function test_iterating_over_the_stream_segments(): void
    {
        $iterator = $this->buildIterator("AAA+1'BBB+2'");

        $names = [];

        foreach ($iterator as $key => $segment) {
            $names[$key] = $segment->name();
        }

        static::assertSame([0 => 'AAA', 1 => 'BBB'], $names);
    }

    public function test_the_una_segment_is_skipped_while_iterating(): void
    {
        $iterator = $this->buildIterator("UNA:+.? 'AAA+1'BBB+2'");

        $names = [];

        foreach ($iterator as $segment) {
            $names[] = $segment->name();
        }

        static::assertSame(['AAA', 'BBB'], $names);
    }

    public function test_providing_the_current_segline(): void
    {
        $iterator = $this->buildIterator("AAA+1'BBB+2'");
        $iterator->rewind();

        static::assertSame('AAA+1', $iterator->currentSegline());

        $iterator->next();

        static::assertSame('BBB+2', $iterator->currentSegline());
    }

    public function test_rewinding_the_iterator(): void
    {
        $iterator = $this->buildIterator("AAA+1'BBB+2'");
        $iterator->rewind();
        $iterator->next();

        static::assertSame(1, $iterator->key());

        $iterator->rewind();

        static::assertSame(0, $iterator->key());
        static::assertSame('AAA', $iterator->current()->name());
    }

    public function test_provides_the_una_segment_of_the_stream(): void
    {
        $iterator = $this->buildIterator('UNA|#,!_"AAA#1"');
        $iterator->rewind();

        static::assertSame('UNA|#,!_"', $iterator->getUnaSegment()->toString());
        static::assertSame('AAA', $iterator->current()->name());
    }

    public function test_throws_when_no_segment_is_available(): void
    {
        $iterator = $this->buildIterator('');
        $iterator->rewind();

        static::assertFalse($iterator->valid());

        $this->expectException(InvalidEdifactContentException::class);
        $iterator->current();
    }

    private function buildIterator(string $ediContent): StreamIterator
    {
        $segmentFactory = new ConcreteSegmentFactory();
        $segmentFactory->addFallback(GenericSegment::class);

        $stream = Stream::fromString($ediContent);
        $segmentFactory->setUnaSegment($stream->getUnaSegment());

        return new StreamIterator($stream, $segmentFactory);
    }
}
