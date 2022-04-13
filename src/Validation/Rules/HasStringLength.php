<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation\Rules;

final class HasStringLength
{
    public function __construct(
        private int|null $min = null,
        private int|null $max = null
    ) {
    }

    public function min(int|null $min = null): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(int|null $max = null): self
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
        if ($this->min === null) {
            return true;
        }

        return $shouldLength >= $this->min;
    }

    private function checkMax(int $shouldLength): bool
    {
        if ($this->max === null) {
            return true;
        }

        return $shouldLength <= $this->max;
    }
}
