<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Apfelfrisch\Edifact\Stream\Stream;
use Apfelfrisch\Edifact\Stream\StreamIterator;
use Closure;
use Generator;

class Message
{
    protected Stream $stream;

    protected StreamIterator $segmentIterator;

    protected SegmentFactory $segmentFactory;

    public function __construct(Stream $stream, ?SegmentFactory $segmentFactory = null)
    {
        $this->stream = $stream;
        $this->segmentFactory = $segmentFactory ?? SegmentFactory::fromDefault();
        $this->segmentFactory->setUnaSegment($this->stream->getUnaSegment());
        $this->segmentIterator = new StreamIterator($this->stream, $this->segmentFactory);
    }

    public static function fromFilepath(string $filepath, ?SegmentFactory $segmentFactory = null): self
    {
        $stream = new Stream($filepath);

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

    public function getFilepath(): string|null
    {
        if (false !== $filepath = $this->stream->getRealPath()) {
            return $filepath;
        }

        return null;
    }

    public function getSegments(): StreamIterator
    {
        $this->segmentIterator->rewind();

        return $this->segmentIterator;
    }

    /**
     * @psalm-return list<SegmentInterface>
     */
    public function getAllSegments(): array
    {
        return $this->getSegments()->getAll();
    }

    /**
     * @template T of SegmentInterface
     * @psalm-param class-string<T> $segmentClass
     * @psalm-suppress InvalidReturnType
     * @psalm-return Generator<int, T, mixed, void>
     */
    public function filterSegments(string $segmentClass, ?Closure $closure = null): Generator
    {
        foreach ($this->getSegments() as $segment) {
            if ($segment::class !== $segmentClass) {
                continue;
            }
            if ($closure === null || $closure($segment) === true) {
                yield $segment;
            }
        }
    }

    /**
     * @template T of SegmentInterface
     * @psalm-param class-string<T> $segmentClass
     * @psalm-return list<T>
     */
    public function filterAllSegments(string $segmentClass, ?Closure $closure = null): array
    {
        /** @var list<T> */
        return iterator_to_array($this->filterSegments($segmentClass, $closure));
    }

    /**
     * @template T of SegmentInterface
     * @psalm-param class-string<T> $segmentClass
     * @psalm-return T|null
     */
    public function findFirstSegment(string $segmentClass, ?Closure $closure = null): ?SegmentInterface
    {
        foreach ($this->filterSegments($segmentClass, $closure) as $segment) {
            return $segment;
        }

        return null;
    }

    /**
     * @psalm-return Generator<self>
     */
    public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    {
        $this->segmentIterator->rewind();

        $stream = null;

        while ($this->segmentIterator->valid()) {
            $segLine = $this->segmentIterator->currentSegline();
            $this->segmentIterator->next();

            $segmentName = substr($segLine, 0, 3);

            if ($segmentName === $header) {
                $stream = new Stream('php://temp', 'w+', $this->getUnaSegment());
            }

            if ($stream === null) {
                continue;
            }

            $stream->write($segLine.$this->getUnaSegment()->segmentTerminator());

            if ($segmentName === $trailer) {
                yield new self($stream, $this->segmentFactory);

                $stream = null;
            }
        }
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->stream->getUnaSegment();
    }

    /**
     * @psalm-return list<array<string, array<string, string|null>>>
     */
    public function toArray(): array
    {
        return array_map(function(SegmentInterface $segment): array {
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
}
