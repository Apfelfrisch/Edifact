<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Interfaces;

use Apfelfrisch\Edifact\Elements;
use Iterator;

interface SegValidatorInterface
{
    /** @psalm-return Iterator<\Apfelfrisch\Edifact\Validation\Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator;
}
