<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment\Iterator;

use Apfelfrisch\Edifact\Segment\SegmentFactory;

interface IteratorFactory
{
    public function fromString(SegmentFactory $segmentFactory, string $string): SegmentIterator;

    public function fromFilepath(SegmentFactory $segmentFactory, string $filepath): SegmentIterator;
}
