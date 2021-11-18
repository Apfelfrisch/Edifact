<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\DataGroups;
use Proengeno\Edifact\SegmentData;

interface SegValidatorInterface
{
    public function validate(DataGroups $blueprint, SegmentData $elements): self;
}
