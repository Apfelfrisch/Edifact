<?php

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\SegValidationException;

class SegmentValidator implements SegValidatorInterface
{
    const ALPHA = 'a';
    const NUMERIC = 'n';
    const ALPHA_NUMERIC = 'an';

    /**
     * @param array<string, array<string, string>> $blueprint
     * @param array<string, array<string, null|string>> $elements
     */
    public function validate(array $blueprint, array $elements): SegValidatorInterface
    {
        foreach ($blueprint as $dataGroupKey => $dataGroup) {
            foreach ($dataGroup as $dataKey => $validation) {
                if ($validation !== null) {
                    list($necessaryStatus, $type, $lenght) = explode('|', $validation);

                    if ($this->isDatafieldOptional($necessaryStatus) && !$this->isDataIsAvailable($elements, $dataGroupKey, $dataKey)) {
                        $this->cleanUp($elements, $dataGroupKey, $dataKey);
                        continue;
                    }

                    $this->checkAvailability($elements, $dataGroupKey, $dataKey);
                    $this->checkStringType($type, $elements, $dataGroupKey, $dataKey);
                    $this->checkStringLenght($lenght, $elements, $dataGroupKey, $dataKey);
                }
                $this->cleanUp($elements, $dataGroupKey, $dataKey);
            }
            $this->checkUnknowDatafields($data[$dataGroupKey] ?? []);
            $this->cleanUp($data, $dataGroupKey);
        }
        $this->checkUnknowDataGroup($elements);

        return $this;
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function checkUnknowDataGroup(array $elements): void
    {
        if (empty($elements)) {
            return;
        }

        $keys = array_keys($elements);
        throw SegValidationException::forKey(array_shift($keys), 'Data-Group not allowed.', 7);
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function checkUnknowDatafields(array $elements, string $dataGroupKey): void
    {
        $element = $elements[$dataGroupKey] ?? [];

        if ($element === []) {
            return;
        }

        $key = current(array_keys($element));
        $value = current($element);

        throw SegValidationException::forKeyValue($key, $value ?? 'null', 'Data-Element not allowed.', 6);
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function cleanUp(array &$elements, string $dataGroupKey, string $dataKey = null): void
    {
        if ($data === []) {
            return;
        }

        if ($dataKey === null) {
            if (array_key_exists($dataGroupKey, $elements)) {
                unset($elements[$dataGroupKey]);
            }

            return;
        }

        if (array_key_exists($dataKey, $elements[$dataGroupKey])) {
            unset($elements[$dataGroupKey][$dataKey]);
        }
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function isDataIsAvailable(array $elements, string $dataGroupKey, string $dataKey): bool
    {
        return ($elements[$dataGroupKey][$dataKey] ?? '') !== '';
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function isDatafieldIsAvailable(array $elements, string $dataGroupKey, string $dataKey): bool
    {
        return isset($elements[$dataGroupKey][$dataKey]);
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function checkAvailability(array $elements, string $dataGroupKey, string $dataKey): void
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

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function checkStringType(?string $type, array $elements, string $dataGroupKey, string $dataKey): void
    {
        $string = $elements[$dataGroupKey][$dataKey] ?? '';

        /** @psalm-suppress DocblockTypeContradiction: Doublecheck - Type cannot be enforce be php*/
        if (! is_string($string)) {
            throw SegValidationException::forKey($dataKey, 'Data-Element is not a string.', 6);
        }

        if ($type == static::ALPHA_NUMERIC || $type == null) {
            return;
        }
        if ($type == static::NUMERIC && !is_numeric($string)) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element contains non-numeric characters.', 2);
        }
        if ($type == static::ALPHA && !ctype_alpha(str_replace(' ', '', $string))) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element contains non-alpha characters.', 3);
        }
    }

    /**
     * @param array<string, array<string, null|string>> $elements
     */
    private function checkStringLenght(string $lenght, array $elements, string $dataGroupKey, string $dataKey): void
    {
        $string = $elements[$dataGroupKey][$dataKey] ?? '';

        /** @psalm-suppress DocblockTypeContradiction: Doublecheck - Type cannot be enforce be php*/
        if (! is_string($string)) {
            throw SegValidationException::forKey($dataKey, 'Data-Element is not a string.', 6);
        }

        $strLen = strlen($string);
        if ($strLen == 0) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element unavailable or empty.', 4);
        }
        if ($lenght < $strLen) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Data-Element has more than' . $lenght . ' Characters.', 5);
        }
    }
}
