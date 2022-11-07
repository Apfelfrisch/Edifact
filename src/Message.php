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

class Message
{
    protected static MessageFactory|null $factory;

    public function __construct(
        protected SegmentIterator $segmentIterator,
        protected Formatter $formatter,
    ) {
    }

    public static function setFactory(MessageFactory $factory): void
    {
        self::$factory = $factory;
    }

    public static function getFactory(): MessageFactory
    {
        return self::$factory ?? new StreamMessageFactory;
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
     * @phpstan-return list<Segment>
     */
    public function getAllSegments(): array
    {
        return array_values(iterator_to_array($this->getSegments()));
    }

    /**
     * @template T of Segment
     * @param class-string<T> $segmentClass
     *
     * @phpstan-return Generator<int, T, null, void>
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
     * @phpstan-param class-string<T> $segmentClass
     * @phpstan-return list<T>
     */
    public function filterAllSegments(string $segmentClass, ?Closure $closure = null): array
    {
        /** @var list<T> */
        return iterator_to_array($this->filterSegments($segmentClass, $closure));
    }

    /**
     * @template T of Segment
     * @phpstan-param class-string<T> $segmentClass
     * @phpstan-return T|null
     */
    public function findFirstSegment(string $segmentClass, ?Closure $closure = null): ?Segment
    {
        foreach ($this->filterSegments($segmentClass, $closure) as $segment) {
            return $segment;
        }

        return null;
    }

    /**
     * @return Generator<self>
     */
    public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    {
        $loopCount = -1;
        foreach ($this->segmentIterator as $segment) {
            $loopCount++;

            $offset ??= $segment->name() === $header ? $loopCount : null;

            if ($offset === null) {
                continue;
            }

            if ($segment->name() === $trailer) {
                yield new self(
                    new PartialIterator($this->getUnaSegment(), $this->segmentIterator, $offset, $loopCount + 1 - $offset),
                    $this->formatter
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
     * @return list<array<string, array<string, string|null>>>
     */
    public function toArray(): array
    {
        return array_map(function (Segment $segment): array {
            return $segment->toArray();
        }, $this->getAllSegments());
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
