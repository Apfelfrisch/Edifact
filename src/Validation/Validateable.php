<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Iterator;

interface Validateable
{
    /** @psalm-return Iterator<\Apfelfrisch\Edifact\Validation\Failure> */
    public function validate(SegmentValidator $segmentValidator): Iterator;
}
