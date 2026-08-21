<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Iterators;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentIterator;
use Apfelfrisch\Edifact\Segment\UnaSegment;

final class InMemoryIterator implements SegmentIterator
{
    private int $currentSegmentNumber = 0;

    private readonly UnaSegment $unaSegment;

    /**
     * @param list<Segment> $segments
     */
    public function __construct(
        private readonly array $segments = [],
        null|UnaSegment $unaSegment = null,
    ) {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
    }

    public static function fromSegments(Segment ...$segments): self
    {
        return new self(array_values($segments));
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment;
    }

    public function current(): Segment
    {
        return (
            $this->segments[$this->currentSegmentNumber] ?? throw InvalidEdifactContentException::noSegmentAvailable()
        );
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegmentNumber++;
    }

    public function rewind(): void
    {
        $this->currentSegmentNumber = 0;
    }

    public function valid(): bool
    {
        return array_key_exists($this->currentSegmentNumber, $this->segments);
    }
}
