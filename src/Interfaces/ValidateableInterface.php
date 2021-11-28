<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Interfaces;

use Apfelfrisch\Edifact\Validation\SegmentValidator;
use Iterator;

interface ValidateableInterface
{
    /** @psalm-return Iterator<\Apfelfrisch\Edifact\Validation\Failure> */
    public function validate(SegmentValidator $segmentValidator): Iterator;
}
