<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment\Iterator\Stream;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Iterator\SegmentIterator;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Segment\Iterator\Stream\Stream;

/**
 * @implements Iterator<int, SegmentInterface>
 */
final class StreamIterator implements SegmentIterator
{
    private int $currentSegmentNumber = 0;
    private string $segline = '';

    public function __construct(
        private Stream $stream,
        private SegmentFactory $segmentFactory
    ) {
        $stream->rewind();
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->stream->getUnaSegment();
    }

    public function currentSegline(): string
    {
        return $this->segline;
    }

    public function current(): Segment
    {
        if (! $this->valid()) {
            return throw InvalidEdifactContentException::noSegmentAvailable();
        }

        return $this->getSegmentObject($this->currentSegline());
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegmentNumber++;

        $this->segline = $this->stream->getSegment();
    }

    public function rewind(): void
    {
        $this->stream->rewind();
        $this->currentSegmentNumber = 0;
        $this->segline = $this->stream->getSegment();
        if ($this->segline !== '' && str_starts_with($this->segline, UnaSegment::UNA)) {
            $this->segline = $this->stream->getSegment();
        }
    }

    public function valid(): bool
    {
        return $this->segline !== '';
    }

    private function getSegmentObject(string $segLine): Segment
    {
        return $this->segmentFactory->build($segLine);
    }
}
