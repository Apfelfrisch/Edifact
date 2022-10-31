<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\SegmentFactoryInterface;
use Apfelfrisch\Edifact\Stream\Stream;

class MessageFactory
{
    private SegmentFactoryInterface $segmentFactory;

    public function __construct(
        SegmentFactoryInterface $segmentFactory = null
    ) {
        $this->segmentFactory = $segmentFactory ?? SegmentFactory::fromDefault();
    }

    public function fromString(string $string, string $filename = 'php://temp'): Message
    {
        return new Message(Stream::fromString($string, $filename), $this->segmentFactory);
    }

    public function fromStream(Stream $stream): Message
    {
        return new Message($stream, $this->segmentFactory);
    }

    public function fromFilepath(string $filepath): Message
    {
        return new Message(new Stream($filepath), $this->segmentFactory);
    }
}
