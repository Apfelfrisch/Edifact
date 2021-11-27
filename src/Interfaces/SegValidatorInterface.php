<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Interfaces;

use Apfelfrisch\Edifact\Elements;

interface SegValidatorInterface
{
    public function validate(Elements $blueprint, Elements $elements): self;
}
