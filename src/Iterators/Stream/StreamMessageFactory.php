<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Iterators\Stream;

use Apfelfrisch\Edifact\Formatter\EdifactFormatter;
use Apfelfrisch\Edifact\Message;
use Apfelfrisch\Edifact\MessageFactory;
use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\SegmentFactory;

class StreamMessageFactory implements MessageFactory
{
    private SegmentFactory $segmentFactory;

    public function __construct(
        SegmentFactory $segmentFactory = null
    ) {
        $this->segmentFactory = $segmentFactory ?? new ConcreteSegmentFactory;
    }

    public function fromString(string $string): Message
    {
        $stream = Stream::fromString($string, 'php://temp');

        $this->segmentFactory->setUnaSegment($stream->getUnaSegment());

        return new Message(new StreamIterator($stream, $this->segmentFactory), new EdifactFormatter($stream->getUnaSegment()));
    }

    public function fromFilepath(string $filepath): Message
    {
        $stream = new Stream($filepath);

        return new Message(new StreamIterator($stream, $this->segmentFactory), new EdifactFormatter($stream->getUnaSegment()));
    }
}
