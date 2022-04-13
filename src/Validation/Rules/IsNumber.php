<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation\Rules;

final class IsNumber
{
    public function __invoke(string $value): bool
    {
        if (! is_numeric($value)) {
            return false;
        }

        return ! is_nan((float) $value);
    }
}
