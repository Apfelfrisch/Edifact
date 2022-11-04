<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Formatter\Formatter;
use Apfelfrisch\Edifact\Segment\Iterator\SegmentIterator;
use Apfelfrisch\Edifact\Segment\Segment;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Closure;
use Generator;

class Message
{
    private static MessageFactory|null $factory;

    public function __construct(
        protected SegmentIterator $segmentIterator,
        protected Formatter $formatter,
    ) {
    }

    public static function setFactory(MessageFactory $factory)
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
     * @psalm-return list<SegmentInterface>
     */
    public function getAllSegments(): array
    {
        return array_values(iterator_to_array($this->getSegments()));
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
    public function findFirstSegment(string $segmentClass, ?Closure $closure = null): ?Segment
    {
        foreach ($this->filterSegments($segmentClass, $closure) as $segment) {
            return $segment;
        }

        return null;
    }

    /**
     * @psalm-return Generator<self>
     */
    // public function unwrap(string $header = 'UNH', string $trailer = 'UNT'): Generator
    // {
    //     $this->segmentIterator->rewind();

    //     $stream = null;

    //     while ($this->segmentIterator->valid()) {
    //         $segLine = $this->segmentIterator->currentSegline();
    //         $this->segmentIterator->next();

    //         $segmentName = substr($segLine, 0, 3);

    //         if ($segmentName === $header) {
    //             $stream = new Stream('php://temp', 'w+', $this->getUnaSegment());
    //         }

    //         if ($stream === null) {
    //             continue;
    //         }

    //         $stream->write(
    //             str_replace(
    //                 $this->getUnaSegment()->segmentTerminator(),
    //                 $this->getUnaSegment()->escapeCharacter().$this->getUnaSegment()->segmentTerminator(),
    //                 $segLine
    //             ).$this->getUnaSegment()->segmentTerminator()
    //         );

    //         if ($segmentName === $trailer) {
    //             yield new self($stream, $this->segmentFactory);

    //             $stream = null;
    //         }
    //     }
    // }

    public function getUnaSegment(): UnaSegment
    {
        return $this->segmentIterator->getUnaSegment();
    }

    /**
     * @psalm-return list<array<string, array<string, string|null>>>
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
