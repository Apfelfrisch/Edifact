<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\SegValidationException;
use Proengeno\Edifact\Message\DataGroupCollection;

class SegmentValidator implements SegValidatorInterface
{
    const ALPHA = 'a';
    const NUMERIC = 'n';
    const ALPHA_NUMERIC = 'an';

    /**
     * @param array<string, array<string, string>> $blueprint
     */
    public function validate(array $blueprint, DataGroupCollection $elements): SegValidatorInterface
    {
        foreach ($blueprint as $dataGroupKey => $dataGroup) {
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

    private function isDataIsAvailable(DataGroupCollection $elements, string $dataGroupKey, string $dataKey): bool
    {
        return ($elements->getValue($dataGroupKey, $dataKey) ?? '') !== '';
    }

    private function isDatafieldIsAvailable(DataGroupCollection $elements, string $dataGroupKey, string $dataKey): bool
    {
        return $elements->getValue($dataGroupKey, $dataKey) !== null;
    }

    private function checkAvailability(DataGroupCollection $elements, string $dataGroupKey, string $dataKey): void
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

    private function checkStringType(?string $type, DataGroupCollection $elements, string $dataGroupKey, string $dataKey): void
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

    private function checkStringLenght(string $lenght, DataGroupCollection $elements, string $dataGroupKey, string $dataKey): void
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
