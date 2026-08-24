<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Iterators\Stream\StreamIterator;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\Segment;

final class StreamMessageFactory implements MessageFactory
{
    /** @var array<string, array<mixed>> */
    private array $streamFilters = [];

    private readonly ConcreteSegmentFactory $segmentFactory;

    public function __construct()
    {
        $this->segmentFactory = new ConcreteSegmentFactory();
    }

    /** @param array<mixed> $params */
    public function addStreamFilter(string $filter, array $params = []): self
    {
        $this->streamFilters[$filter] ??= $params;

        return $this;
    }

    /**
     * @param class-string<Segment> $segmentClass
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        $this->segmentFactory->addSegment($name, $segmentClass);

        return $this;
    }

    /**
     * @param class-string<Segment> $fallback
     */
    public function addFallback(string $fallback): self
    {
        $this->segmentFactory->addFallback($fallback);

        return $this;
    }

    public function markAsDefault(): void
    {
        Message::setFactory($this);
    }

    public function fromString(string $string): Message
    {
        return $this->fromStream($this->applyStreamFilters(Stream::fromString($string, 'php://temp')));
    }

    public function fromFilepath(string $filepath): Message
    {
        return $this->fromStream($this->applyStreamFilters(new Stream($filepath)));
    }

    /**
     * Builds a message on top of an existing stream, e.g. one yielded by
     * Stream::unwrap(). The stream is used as is: the factory's stream
     * filters are only applied to streams the factory opens itself.
     */
    public function fromStream(Stream $stream): Message
    {
        // A per-message factory keeps a later fromString()/fromFilepath() call
        // with different UNA characters from corrupting this message.
        return new Message(
            new StreamIterator($stream, $this->segmentFactory->withUnaSegment($stream->getUnaSegment())),
            new EdifactFormatter($stream->getUnaSegment()),
        );
    }

    private function applyStreamFilters(Stream $stream): Stream
    {
        foreach ($this->streamFilters as $filter => $params) {
            $stream->addReadFilter($filter, $params);
        }

        return $stream;
    }
}
