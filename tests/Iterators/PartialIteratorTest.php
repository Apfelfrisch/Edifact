<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Test\Iterators;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Iterators\PartialIterator;
use Apfelfrisch\Edifact\Segment\GenericSegment;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Test\TestCase;
use ArrayIterator;

final class PartialIteratorTest extends TestCase
{
    public function test_iterating_over_the_full_inner_iterator(): void
    {
        $iterator = new PartialIterator(new UnaSegment(), $this->buildSegments('AAA', 'BBB'));

        static::assertSame(['AAA', 'BBB'], $this->names($iterator));
    }

    public function test_iterating_a_slice_with_offset_and_limit(): void
    {
        $iterator = new PartialIterator(new UnaSegment(), $this->buildSegments('AAA', 'BBB', 'CCC', 'DDD'), 1, 2);

        static::assertSame(['BBB', 'CCC'], $this->names($iterator));
    }

    public function test_keys_start_at_zero_for_every_slice(): void
    {
        $iterator = new PartialIterator(new UnaSegment(), $this->buildSegments('AAA', 'BBB', 'CCC'), 1, 2);

        $keys = [];

        foreach ($iterator as $key => $_segment) {
            $keys[] = $key;
        }

        static::assertSame([0, 1], $keys);
    }

    public function test_rewinding_the_iterator(): void
    {
        $iterator = new PartialIterator(new UnaSegment(), $this->buildSegments('AAA', 'BBB'));

        static::assertSame(['AAA', 'BBB'], $this->names($iterator));

        $iterator->rewind();

        static::assertSame(0, $iterator->key());
        static::assertSame('AAA', $iterator->current()->name());
    }

    public function test_provides_the_una_segment(): void
    {
        $unaSegment = new UnaSegment('|', '#');
        $iterator = new PartialIterator($unaSegment, $this->buildSegments('AAA'));

        static::assertSame($unaSegment, $iterator->getUnaSegment());
    }

    public function test_throws_when_no_segment_is_available(): void
    {
        $iterator = new PartialIterator(new UnaSegment(), $this->buildSegments());
        $iterator->rewind();

        $this->expectException(InvalidEdifactContentException::class);
        $iterator->current();
    }

    /**
     * @return ArrayIterator<int, GenericSegment>
     */
    private function buildSegments(string ...$names): ArrayIterator
    {
        return new ArrayIterator(array_values(array_map(
            static fn(string $name): GenericSegment => GenericSegment::fromAttributes($name, ['1']),
            $names,
        )));
    }

    /**
     * @return list<string>
     */
    private function names(PartialIterator $iterator): array
    {
        $names = [];

        foreach ($iterator as $segment) {
            $names[] = $segment->name();
        }

        return $names;
    }
}
