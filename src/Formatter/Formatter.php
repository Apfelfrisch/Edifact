<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Formatter;

use Apfelfrisch\Edifact\Segment\Segment;

interface Formatter
{
    public function prefixUna(): self;

    public function format(Segment ...$segments): string;
}
