<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Interfaces\SegValidatorInterface;
use Apfelfrisch\Edifact\Exceptions\SegValidationException;
use Apfelfrisch\Edifact\Elements;

class SegmentValidator implements SegValidatorInterface
{
    const ALPHA = 'a';
    const NUMERIC = 'n';
    const ALPHA_NUMERIC = 'an';

    public function validate(Elements $blueprint, Elements $elements): SegValidatorInterface
    {
        foreach ($blueprint->toArray() as $elementKey => $element) {
            foreach ($element as $dataKey => $validation) {
                if ($validation !== null) {
                    list($necessaryStatus, $type, $lenght) = explode('|', $validation);

                    if ($this->isDatafieldOptional($necessaryStatus)) {
                        if (! $this->isDataIsAvailable($elements, $elementKey, $dataKey)) {
                            continue;
                        }
                    }

                    $this->checkAvailability($elements, $elementKey, $dataKey);
                    $this->checkStringType($type, $elements, $elementKey, $dataKey);
                    $this->checkStringLenght($lenght, $elements, $elementKey, $dataKey);
                }
            }
        }

        return $this;
    }

    private function isDataIsAvailable(Elements $elements, string $elementKey, string $dataKey): bool
    {
        return ($elements->getValue($elementKey, $dataKey) ?? '') !== '';
    }

    private function isDatafieldIsAvailable(Elements $elements, string $elementKey, string $dataKey): bool
    {
        return $elements->getValue($elementKey, $dataKey) !== null;
    }

    private function checkAvailability(Elements $elements, string $elementKey, string $dataKey): void
    {
        if ($this->isDatafieldIsAvailable($elements, $elementKey, $dataKey)) {
            return;
        }

        throw SegValidationException::forKey($dataKey, 'Data-Element not available, but needed.', 1);
    }

    private function isDatafieldOptional(?string $necessaryStatus): bool
    {
        return !($necessaryStatus === 'M' || $necessaryStatus === 'R');
    }

    private function checkStringType(?string $type, Elements $elements, string $elementKey, string $dataKey): void
    {
        $string = $elements->getValue($elementKey, $dataKey) ?? '';

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

    private function checkStringLenght(string $lenght, Elements $elements, string $elementKey, string $dataKey): void
    {
        $string = $elements->getValue($elementKey, $dataKey) ?? '';

        $strLen = strlen($string);

        if ($strLen === 0) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element unavailable or empty.', 4);
        }

        if ($lenght < $strLen) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element has more than' . $lenght . ' Characters.', 5);
        }
    }
}
