<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Iterators\Stream\Stream;
use Apfelfrisch\Edifact\Iterators\Stream\StreamIterator;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\MessageFactory;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\Segment;

class StreamMessageFactory implements MessageFactory
{
    /** @var array<string, array<mixed>> */
    private array $streamFilters = [];

    private ConcreteSegmentFactory $segmentFactory;

    public function __construct()
    {
        $this->segmentFactory = new ConcreteSegmentFactory;
    }

    /** @param array<mixed> $params*/
    public function addStreamFilter(string $filter, array $params = []): self
    {
        if (! isset($this->streamFilters[$filter])) {
            $this->streamFilters[$filter] = $params;
        }

        return $this;
    }

    /**
     * @phpstan-param class-string<Segment> $segmentClass
     */
    public function addSegment(string $name, string $segmentClass): self
    {
        $this->segmentFactory->addSegment($name, $segmentClass);

        return $this;
    }

    /**
     * @phpstan-param class-string<Segment> $fallback
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
        return $this->buildMessage(Stream::fromString($string, 'php://temp'));
    }

    public function fromFilepath(string $filepath): Message
    {
        return $this->buildMessage(new Stream($filepath));
    }

    private function buildMessage(Stream $stream): Message
    {
        foreach ($this->streamFilters as $filter => $params) {
            $stream->addReadFilter($filter, $params);
        }

        $this->segmentFactory->setUnaSegment($stream->getUnaSegment());

        return new Message(new StreamIterator($stream, $this->segmentFactory), new EdifactFormatter($stream->getUnaSegment()));
    }
}
