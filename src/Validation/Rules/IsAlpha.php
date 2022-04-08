<?php

declare(strict_types = 1);

namespace Apfelfrisch\Edifact\Validation\Rules;

final class IsAlpha
{
    public function __invoke(string $value): bool
    {
        return ctype_alpha($value);
    }
}
