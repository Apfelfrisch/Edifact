<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Message\DataGroupCollection;

interface SegValidatorInterface
{
    /**
     * @param array<string, array<string, string>> $blueprint
     *
     * @return SegValidatorInterface
     */
    public function validate(array $blueprint, DataGroupCollection $elements): self;
}
