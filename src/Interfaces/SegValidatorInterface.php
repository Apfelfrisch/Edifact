<?php

namespace Proengeno\Edifact\Interfaces;

use Proengeno\Edifact\Validation\SegmentValidator;

interface SegValidatorInterface
{
    /**
     * @param array $blueprint
     * @param array $data
     *
     * @return SegValidatorInterface
     */
    public function validate($blueprint, $data);
}
