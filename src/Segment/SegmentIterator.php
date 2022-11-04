<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use Iterator;

/** @extends Iterator<int, Segment> */
interface SegmentIterator extends Iterator
{
    public function getUnaSegment(): UnaSegment;

    public function current(): Segment;
}
