<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\DataGroups;
use Proengeno\Edifact\Message\SegmentData;

interface SegValidatorInterface
{
    public function validate(DataGroups $blueprint, SegmentData $elements): self;
}
