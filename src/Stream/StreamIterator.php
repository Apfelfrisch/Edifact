<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Stream;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Iterator;

final class StreamIterator implements Iterator
{
    private int $currentSegmentNumber = 0;
    private string $segline = '';

    public function __construct(
        private Stream $stream,
        private SegmentFactory $segmentFactory
    ) {
        $stream->rewind();
    }

    /**
     * @psalm-return list<SegmentInterface>
     */
    public function getAll(): array
    {
        /** @var list<SegmentInterface> */
        return iterator_to_array($this);
    }

    public function currentSegline(): string
    {
        return $this->segline;
    }

    public function current(): SegmentInterface
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

    private function getSegmentObject(string $segLine): SegmentInterface
    {
        return $this->segmentFactory->build($segLine);
    }
}
