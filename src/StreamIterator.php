<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Iterator;

final class StreamIterator implements Iterator
{
    private int $currentSegmentNumber = 0;
    private ?string $segline;

    public function __construct(
        private Stream $stream,
        private SegmentFactory $segmentFactory
    ) {
        $this->segline = $this->getNextSegLine();
    }

    public function getFirst(): ?SegInterface
    {
        $this->rewind();

        if (! $this->valid()) {
            return null;
        }

        return $this->current();
    }

    public function getCurrent(): ?SegInterface
    {
        if (! $this->valid()) {
            return null;
        }

        return $this->current();
    }

    /**
     * @psalm-return list<SegInterface>
     */
    public function getAll(): array
    {
        return array_values(iterator_to_array($this));
    }

    public function currentSegline(): string
    {
        return (string)$this->segline;
    }

    public function current(): SegInterface
    {
        return $this->getSegmentObject($this->currentSegline());
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegmentNumber++;

        $this->segline = $this->getNextSegLine();
    }

    public function rewind(): void
    {
        $this->stream->rewind();
        $this->currentSegmentNumber = 0;
        $this->segline = $this->getNextSegLine();
    }

    public function valid(): bool
    {
        return $this->segline !== null;
    }

    private function getSegmentObject(string $segLine): SegInterface
    {
        return $this->segmentFactory->build($segLine);
    }

    private function getNextSegLine(): ?string
    {
        if ('' !== $segline = $this->stream->getSegment()) {
            return $segline;
        }
        return null;
    }
}
