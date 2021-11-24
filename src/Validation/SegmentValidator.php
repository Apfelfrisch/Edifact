<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\DataGroups;

class SegmentValidator implements SegValidatorInterface
{
    const ALPHA = 'a';
    const NUMERIC = 'n';
    const ALPHA_NUMERIC = 'an';

    public function validate(DataGroups $blueprint, DataGroups $elements): SegValidatorInterface
    {
        foreach ($blueprint->toArray() as $dataGroupKey => $dataGroup) {
            foreach ($dataGroup as $dataKey => $validation) {
                if ($validation !== null) {
                    list($necessaryStatus, $type, $lenght) = explode('|', $validation);

                    if ($this->isDatafieldOptional($necessaryStatus)) {
                        if (! $this->isDataIsAvailable($elements, $dataGroupKey, $dataKey)) {
                            continue;
                        }
                    }

                    $this->checkAvailability($elements, $dataGroupKey, $dataKey);
                    $this->checkStringType($type, $elements, $dataGroupKey, $dataKey);
                    $this->checkStringLenght($lenght, $elements, $dataGroupKey, $dataKey);
                }
            }
        }

        return $this;
    }

    private function isDataIsAvailable(DataGroups $elements, string $dataGroupKey, string $dataKey): bool
    {
        return ($elements->getValue($dataGroupKey, $dataKey) ?? '') !== '';
    }

    private function isDatafieldIsAvailable(DataGroups $elements, string $dataGroupKey, string $dataKey): bool
    {
        return $elements->getValue($dataGroupKey, $dataKey) !== null;
    }

    private function checkAvailability(DataGroups $elements, string $dataGroupKey, string $dataKey): void
    {
        if ($this->isDatafieldIsAvailable($elements, $dataGroupKey, $dataKey)) {
            return;
        }

        throw SegValidationException::forKey($dataKey, 'Data-Element not available, but needed.', 1);
    }

    private function isDatafieldOptional(?string $necessaryStatus): bool
    {
        return !($necessaryStatus === 'M' || $necessaryStatus === 'R');
    }

    private function checkStringType(?string $type, DataGroups $elements, string $dataGroupKey, string $dataKey): void
    {
        $string = $elements->getValue($dataGroupKey, $dataKey) ?? '';

        if ($type === static::ALPHA_NUMERIC || $type == null) {
            return;
        }
        if ($type === static::NUMERIC && ! is_numeric($string)) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element contains non-numeric characters.', 2);
        }
        if ($type === static::ALPHA && ! ctype_alpha(str_replace(' ', '', $string))) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element contains non-alpha characters.', 3);
        }
    }

    private function checkStringLenght(string $lenght, DataGroups $elements, string $dataGroupKey, string $dataKey): void
    {
        $string = $elements->getValue($dataGroupKey, $dataKey) ?? '';

        $strLen = strlen($string);

        if ($strLen === 0) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element unavailable or empty.', 4);
        }

        if ($lenght < $strLen) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element has more than' . $lenght . ' Characters.', 5);
        }
    }
}
