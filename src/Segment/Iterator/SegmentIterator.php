<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment\Iterator;

use Apfelfrisch\Edifact\Segment\SegmentInterface;
use Apfelfrisch\Edifact\Segment\UnaSegment;
use Iterator;

interface SegmentIterator extends Iterator
{
    public function getUnaSegment(): UnaSegment;

    public function currentSegline(): string;

    public function current(): SegmentInterface;
}
