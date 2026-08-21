<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Iterators;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentIterator;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Iterator;
use LimitIterator;

final class PartialIterator implements SegmentIterator
{
    private int $currentSegmentNumber = 0;

    /** @var LimitIterator<int, Segment, Iterator<int, Segment>> */
    private readonly LimitIterator $segmentIterator;

    /** @param Iterator<int, Segment> $segmentIterator */
    public function __construct(
        private readonly UnaSegment $unaSegment,
        Iterator $segmentIterator,
        int $offset = 0,
        int $limit = -1,
    ) {
        $this->segmentIterator = new LimitIterator($segmentIterator, $offset, $limit);
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment;
    }

    public function current(): Segment
    {
        if (!$this->valid()) {
            throw InvalidEdifactContentException::noSegmentAvailable();
        }

        return $this->segmentIterator->current();
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegmentNumber++;

        $this->segmentIterator->next();
    }

    public function rewind(): void
    {
        $this->segmentIterator->rewind();
        $this->currentSegmentNumber = 0;
    }

    public function valid(): bool
    {
        return $this->segmentIterator->valid();
    }
}
