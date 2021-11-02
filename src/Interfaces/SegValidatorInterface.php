<?php

namespace Proengeno\Edifact\Interfaces;

interface SegValidatorInterface
{
    /**
     * @param array<string, array<string, string>> $blueprint
     * @param array<string, array<string, null|string>> $elements
     *
     * @return SegValidatorInterface
     */
    public function validate(array $blueprint, array $elements): self;
}
