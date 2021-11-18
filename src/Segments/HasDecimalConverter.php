<?php

declare(strict_types = 1);

namespace Proengeno\Edifact\Segments;

trait HasDecimalConverter
{
    private string $decimalSeparator = '.';

    public function setDecimalSeparator(string $decimalSeparator): void
    {
        $this->decimalSeparator = $decimalSeparator;
    }

    public function convertToNumeric(string $value): string
    {
        return str_replace($this->decimalSeparator, '.', $value);
    }
}
