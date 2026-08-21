<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Formatter\Formatter;
use Apfelfrisch\Edifact\Iterators\InMemoryIterator;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentIterator;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Closure;
use Generator;
use Stringable;

final class Message implements Stringable
{
    private static null|MessageFactory $factory = null;

    /**
     * Parsed segments, retained after the first materializing call
     * (getAllSegments, toArray, toString) so later calls skip re-parsing
     * the underlying stream. Plain iteration via getSegments() keeps
     * streaming and does not build this cache.
     *
     * @var list<Segment>|null
     */
    private null|array $cachedSegments = null;

    public function __construct(
        private readonly SegmentIterator $segmentIterator,
        private readonly Formatter $formatter,
    ) {}

    public static function setFactory(MessageFactory $factory): void
    {
        self::$factory = $factory;
    }

    public static function getFactory(): MessageFactory
    {
        return self::$factory ?? new StreamMessageFactory();
    }

    public static function fromFilepath(string $filepath): self
    {
        return self::getFactory()->fromFilepath($filepath);
    }

    public static function fromString(string $string): self
    {
        return self::getFactory()->fromString($string);
    }

    public function getSegments(): SegmentIterator
    {
        if ($this->cachedSegments !== null) {
            return new InMemoryIterator($this->cachedSegments, $this->getUnaSegment());
        }

        $this->segmentIterator->rewind();

        return $this->segmentIterator;
    }

    /**
     * @return list<Segment>
     */
    public function getAllSegments(): array
    {
        return $this->cachedSegments ??= iterator_to_array($this->getSegments(), false);
    }

    /**
     * @template T of Segment
     *
     * @param class-string<T> $segmentClass
     *
     * @return Generator<int, T, null, void>
     */
    public function filterSegments(string $segmentClass, null|Closure $closure = null): Generator
    {
        foreach ($this->getSegments() as $segment) {
            if ($segment::class !== $segmentClass) {
                continue;
            }

            /** @var T $segment */
            if ($closure === null || $closure($segment) === true) {
                yield $segment;
            }
        }
    }

    /**
     * @template T of Segment
     *
     * @param class-string<T> $segmentClass
     *
     * @return list<T>
     */
    public function filterAllSegments(string $segmentClass, null|Closure $closure = null): array
    {
        /** @var list<T> */
        return iterator_to_array($this->filterSegments($segmentClass, $closure));
    }

    /**
     * @template T of Segment
     *
     * @param class-string<T> $segmentClass
     *
     * @return T|null
     */
    public function findFirstSegment(string $segmentClass, null|Closure $closure = null): null|Segment
    {
        return $this->filterSegments($segmentClass, $closure)->current();
    }

    /**
     * @return Generator<self>
     */
    public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    {
        $subSegments = null;

        foreach ($this->getSegments() as $segment) {
            $segmentName = $segment->name();

            if ($subSegments === null && $segmentName === $header) {
                $subSegments = [];
            }

            if ($subSegments === null) {
                continue;
            }

            $subSegments[] = $segment;

            if ($segmentName === $trailer) {
                yield new self(new InMemoryIterator($subSegments, $this->getUnaSegment()), $this->formatter);
                $subSegments = null;
            }
        }
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->segmentIterator->getUnaSegment();
    }

    /**
     * @return list<array<array-key, array<array-key, string|null>>>
     */
    public function toArray(): array
    {
        return array_map(static fn(Segment $segment): array => $segment->toArray(), $this->getAllSegments());
    }

    public function toString(): string
    {
        return $this->formatter->format(...$this->getAllSegments());
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
