<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\DataGroups;

interface SegValidatorInterface
{
    public function validate(DataGroups $blueprint, DataGroups $elements): self;
}
