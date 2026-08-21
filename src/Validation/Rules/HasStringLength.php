<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation\Rules;

final class HasStringLength
{
    public function __construct(
        private null|int $min = null,
        private null|int $max = null,
    ) {}

    public function min(null|int $min = null): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(null|int $max = null): self
    {
        $this->max = $max;

        return $this;
    }

    public function __invoke(string $value): bool
    {
        $length = mb_strlen($value);

        return $this->checkMin($length) && $this->checkMax($length);
    }

    private function checkMin(int $shouldLength): bool
    {
        return $this->min === null || $shouldLength >= $this->min;
    }

    private function checkMax(int $shouldLength): bool
    {
        return $this->max === null || $shouldLength <= $this->max;
    }
}
