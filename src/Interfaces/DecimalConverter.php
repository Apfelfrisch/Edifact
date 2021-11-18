<?php

namespace Proengeno\Edifact\Interfaces;

interface DecimalConverter
{
    public function setDecimalSeparator(string $decimalSeparator): void;

    public function convertToNumeric(string $value): string;
}
