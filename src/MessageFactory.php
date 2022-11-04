<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact;

use Apfelfrisch\Edifact\Segment\ConcreteSegmentFactory;
use Apfelfrisch\Edifact\Segment\SegmentFactory;
use Apfelfrisch\Edifact\Segment\Iterator\Stream\Stream;

class MessageFactory
{
    private SegmentFactory $segmentFactory;

    public function __construct(
        SegmentFactory $segmentFactory = null
    ) {
        $this->segmentFactory = $segmentFactory ?? ConcreteSegmentFactory::fromDefault();
    }

    public function fromString(string $string, string $filename = 'php://temp'): Message
    {
        return new Message(Stream::fromString($string, $filename), $this->segmentFactory);
    }

    public function fromFilepath(string $filepath): Message
    {
        return new Message(new Stream($filepath), $this->segmentFactory);
    }
}
