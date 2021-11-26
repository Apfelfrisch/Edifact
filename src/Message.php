<?php

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\SegValidationException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Stream;
use Generator;

class Message implements \Iterator
{
    protected Stream $stream;

    protected SegmentFactory $segmentFactory;

    private int|null $pinnedPointer = null;

    private SegInterface|false $currentSegment = false;

    private int $currentSegmentNumber = -1;

    public function __construct(Stream $stream, ?SegmentFactory $segmentFactory = null)
    {
        $this->stream = $stream;
        $this->rewind();
        $this->segmentFactory = $segmentFactory ?? SegmentFactory::withDefaultDegments();
    }

    public static function fromFilepath(string $string, ?SegmentFactory $segmentFactory = null): self
    {
        $stream = new Stream($string);

        return new self($stream, $segmentFactory);
    }

    public static function fromString(
        string $string, ?SegmentFactory $segmentFactory = null, string $filename = 'php://temp'
    ): self
    {
        $stream = Stream::fromString($string, $filename);

        return new self($stream, $segmentFactory);
    }

    public function addStreamFilter(string $filtername, mixed $params = null): self
    {
        $this->stream->addReadFilter($filtername, $params);

        return $this;
    }

    public function getFilepath(): string
    {
        return $this->stream->getRealPath();
    }

    public function getCurrentSegment(): SegInterface|false
    {
        if ($this->currentSegment === false) {
            $this->currentSegment = $this->getNextSegment();
        }
        return $this->currentSegment;
    }

    public function getNextSegment(): SegInterface|false
    {
        $segLine = $this->getNextSegLine();

        if ($segLine == false) {
            return false;
        }

        return $this->currentSegment = $this->getSegmentObject($segLine);
    }

    /**
     * @psalm-param callable|array<string, string>|null $criteria
     */
    public function findSegmentFromBeginn(string $searchSegment, callable|array|null $criteria = null): SegInterface|false
    {
        $this->rewind();

        return $this->findNextSegment($searchSegment, $criteria);
    }

    /**
     * @psalm-param callable|array<string, string>|null $criteria
     */
    public function findNextSegment(string $searchSegment, callable|array|null $criteria = null): SegInterface|false
    {
        while ($segmentObject = $this->getNextSegment()) {
            if ($segmentObject->name() == $searchSegment) {
                if ($this->checkCriteria($criteria, $segmentObject) === true) {
                    return $segmentObject;
                }
                continue;
            }
        }

        return false;
    }

    /**
     * @psalm-return Generator<self>
     */
    public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    {
        $this->pinPointer();

        $this->rewind();

        $stream = null;

        while ($segLine = $this->getNextSegLine()) {
            $segmentName = substr($segLine, 0, 3);

            if ($segmentName === $header) {
                $stream = new Stream('php://temp', 'w+', $this->getDelimiter());
            }

            if ($stream === null) {
                continue;
            }

            $stream->write($segLine.$this->getDelimiter()->getSegmentTerminator());

            if ($segmentName === $trailer) {
                yield new self($stream, $this->segmentFactory);

                $stream = null;
            }
        }

        $this->jumpToPinnedPointer();
    }

    public function pinPointer(): void
    {
        $this->pinnedPointer = $this->stream->tell();
    }

    public function jumpToPinnedPointer(): int
    {
        if ($this->pinnedPointer === null) {
            return $this->stream->tell();
        }

        $pinnedPointer = $this->pinnedPointer;
        $this->pinnedPointer = null;

        $this->stream->seek($pinnedPointer);

        return $pinnedPointer;
    }

    public function validateSegments(): void
    {
        $this->rewind();

        $segment = false;
        try {
            while ($segment = $this->getNextSegment()) {
                $segment->validate();
            }
        } catch (SegValidationException $e) {
            throw new ValidationException(
                $e->getMessage(), $this->currentSegmentNumber, $segment instanceof SegInterface ? $segment->name() : ''
            );
        }

        $this->rewind();
    }

    public function getDelimiter(): Delimiter
    {
        return $this->stream->getDelimiter();
    }

    public function current(): SegInterface|false
    {
        return $this->getCurrentSegment();
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegment = false;
    }

    public function rewind(): void
    {
        $this->stream->rewind();
        $this->currentSegmentNumber = -1;
        $this->currentSegment = false;
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function toArray(): array
    {
        return array_map(function(SegInterface|false $segment): array {
            return $segment ? $segment->toArray() : [];
        }, iterator_to_array($this));
    }

    public function toString(): string
    {
        return $this->stream->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    protected function getSegmentObject(string $segLine): SegInterface
    {
        return $this->segmentFactory->build($segLine, $this->getDelimiter());
    }

    private function getNextSegLine(): string
    {
        $this->currentSegmentNumber++;

        return $this->stream->getSegmentTerminator();
    }

    /**
     * @psalm-param callable|array<string, string>|null $criteria
     */
    private function checkCriteria(callable|array|null $criteria, SegInterface $segmentObject): bool
    {
        if ($criteria === null) {
            return true;
        }

        if (is_array($criteria)) {
            foreach ($criteria as $getter => $pattern) {
                if ($segmentObject->$getter() != $pattern) {
                    return false;
                }
            }
            return true;
        }

        return (bool)$criteria($segmentObject);
    }
}
