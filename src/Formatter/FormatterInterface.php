<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Formatter;

use Apfelfrisch\Edifact\Segment\SegmentInterface;

interface FormatterInterface
{
    public function prefixUna(): self;

    public function format(SegmentInterface ...$segments): string;
}
