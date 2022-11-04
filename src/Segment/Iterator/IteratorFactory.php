<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment\Iterator;

use Apfelfrisch\Edifact\Segment\SegmentFactoryInterface;

interface IteratorFactory
{
    public function fromString(SegmentFactoryInterface $segmentFactory, string $string): SegmentIterator;

    public function fromFilepath(SegmentFactoryInterface $segmentFactory, string $filepath): SegmentIterator;
}
