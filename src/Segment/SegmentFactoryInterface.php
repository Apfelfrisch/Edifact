<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

interface SegmentFactoryInterface
{
    public function setUnaSegment(UnaSegment $unaSegment): void;

    public function build(string $segline): SegmentInterface;
}
