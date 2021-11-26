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

    private ?SegInterface $currentSegment = null;

    private int $currentSegmentNumber = 0;

    private ?string $nextSegline = null;

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
        if ($this->currentSegment !== null) {
            return $this->currentSegment;
        }

        if (! $this->valid()) {
            return false;
        }

        return $this->currentSegment = $this->current();
    }

    public function getNextSegment(): SegInterface|false
    {
        if (! $this->valid()) {
            return false;
        }

        $this->currentSegment = $this->current();

        $this->next();

        return $this->currentSegment;
    }

    /**
     * @psalm-return list<SegInterface>
     */
    public function getAllSegments(): array
    {
        return array_values(iterator_to_array($this));
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
        $this->rewind();

        $stream = null;

        while (null !== $segLine = $this->getNextSegLine()) {
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
    }

    public function validateSegments(): void
    {
        $this->rewind();

        $segment = false;
        try {
            foreach ($this as $segment) {
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

    public function current(): SegInterface
    {
        return $this->getSegmentObject((string)$this->nextSegline);
    }

    public function key(): int
    {
        return $this->currentSegmentNumber;
    }

    public function next(): void
    {
        $this->currentSegmentNumber++;

        $this->nextSegline = $this->getNextSegLine();
    }

    public function rewind(): void
    {
        $this->stream->rewind();
        $this->currentSegmentNumber = 0;
        $this->currentSegment = null;
        $this->nextSegline = $this->getNextSegLine();
    }

    public function valid(): bool
    {
        return $this->nextSegline !== null;
    }

    public function toArray(): array
    {
        return array_map(function(SegInterface $segment): array {
            return $segment->toArray();
        }, $this->getAllSegments());
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

    private function getNextSegLine(): ?string
    {
        if ('' !== $segline = $this->stream->getSegment()) {
            return $segline;
        }
        return null;
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
