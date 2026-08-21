<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Formatter\Formatter;
use Apfelfrisch\Edifact\Iterators\PartialIterator;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\SegmentIterator;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Closure;
use Generator;
use Stringable;

final class Message implements Stringable
{
    private static ?MessageFactory $factory = null;

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
        $this->segmentIterator->rewind();

        return $this->segmentIterator;
    }

    /**
     * @return list<Segment>
     */
    public function getAllSegments(): array
    {
        return array_values(iterator_to_array($this->getSegments()));
    }

    /**
     * @template T of Segment
     *
     * @param class-string<T> $segmentClass
     *
     * @return Generator<int, T, null, void>
     */
    public function filterSegments(string $segmentClass, ?Closure $closure = null): Generator
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
    public function filterAllSegments(string $segmentClass, ?Closure $closure = null): array
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
    public function findFirstSegment(string $segmentClass, ?Closure $closure = null): ?Segment
    {
        return $this->filterSegments($segmentClass, $closure)->current();
    }

    /**
     * @return Generator<self>
     */
    public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    {
        $offset = null;
        $loopCount = -1;

        foreach ($this->segmentIterator as $segment) {
            $loopCount++;

            $offset ??= $segment->name() === $header ? $loopCount : null;

            if ($offset === null) {
                continue;
            }

            if ($segment->name() === $trailer) {
                yield new self(
                    new PartialIterator(
                        $this->getUnaSegment(),
                        $this->segmentIterator,
                        $offset,
                        $loopCount + 1 - $offset,
                    ),
                    $this->formatter,
                );
                $offset = null;
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
