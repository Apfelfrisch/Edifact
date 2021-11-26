<?php

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Exceptions\SegValidationException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Interfaces\SegInterface;
use Apfelfrisch\Edifact\SegmentFactory;
use Apfelfrisch\Edifact\Stream;
use Generator;
use Iterator;

class Message
{
    protected Stream $stream;

    protected StreamIterator $iterator;

    protected SegmentFactory $segmentFactory;

    private ?SegInterface $currentSegment = null;

    public function __construct(Stream $stream, ?SegmentFactory $segmentFactory = null)
    {
        $this->stream = $stream;
        $this->segmentFactory = $segmentFactory ?? SegmentFactory::withDefaultDegments();
        $this->iterator = new StreamIterator($this->stream, $this->segmentFactory);
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

    public function getSegments(): StreamIterator
    {
        $this->iterator->rewind();

        return $this->iterator;
    }

    public function getCurrentSegment(): SegInterface|false
    {
        if ($this->currentSegment !== null) {
            return $this->currentSegment;
        }

        if (! $this->iterator->valid()) {
            return false;
        }

        return $this->currentSegment = $this->iterator->current();
    }

    public function getNextSegment(): SegInterface|false
    {
        if (! $this->iterator->valid()) {
            return false;
        }

        $this->currentSegment = $this->iterator->current();

        $this->iterator->next();

        return $this->currentSegment;
    }

    /**
     * @psalm-return list<SegInterface>
     */
    public function getAllSegments(): array
    {
        return array_values(iterator_to_array($this->getSegments()));
    }

    /**
     * @psalm-param callable|array<string, string>|null $criteria
     */
    public function findSegmentFromBeginn(string $searchSegment, callable|array|null $criteria = null): SegInterface|false
    {
        $this->iterator->rewind();

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
        $this->iterator->rewind();

        $stream = null;

        while ($this->iterator->valid()) {
            $segLine = $this->iterator->currentSegline();
            $this->iterator->next();

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
        $segment = false;
        try {
            foreach ($this->getSegments() as $segment) {
                $segment->validate();
            }
        } catch (SegValidationException $e) {
            throw new ValidationException(
                $e->getMessage(), $this->iterator->key(), $segment instanceof SegInterface ? $segment->name() : ''
            );
        }
    }

    public function getDelimiter(): Delimiter
    {
        return $this->stream->getDelimiter();
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
