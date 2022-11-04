<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment\Iterator\Stream;

use Apfelfrisch\Edifact\Segment\Iterator\IteratorFactory;
use Apfelfrisch\Edifact\Segment\SegmentFactoryInterface;

final class StreamIteratorFactory implements IteratorFactory
{
    public function fromString(SegmentFactoryInterface $segmentFactory, string $string): StreamIterator
    {
        return new StreamIterator(Stream::fromString($string, 'php://temp'), $segmentFactory);
    }

    public function fromFilepath(SegmentFactoryInterface $segmentFactory, string $filepath): StreamIterator
    {
        return new StreamIterator(new Stream($filepath), $segmentFactory);
    }
}
