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
     * @param array $blueprint
     * @param array $data
     *
     * @return SegValidatorInterface
     */
    public function validate($blueprint, $data)
    {
        foreach ($blueprint as $dataGroupKey => $dataGroup) {
            foreach ($dataGroup as $dataKey => $validation) {
                if ($validation !== null) {
                    list($necessaryStatus, $type, $lenght) = explode('|', $validation);

                    if ($this->isDatafieldOptional($necessaryStatus) && !$this->isDataIsAvailable($data, $dataGroupKey, $dataKey)) {
                        $this->cleanUp($data, $dataGroupKey, $dataKey);
                        continue;
                    }

                    $this->checkAvailability($data, $dataGroupKey, $dataKey);
                    $this->checkStringType($type, $data, $dataGroupKey, $dataKey);
                    $this->checkStringLenght($lenght, $data, $dataGroupKey, $dataKey);
                }
                $this->cleanUp($data, $dataGroupKey, $dataKey);
            }
            $this->checkUnknowDatafields(@$data[$dataGroupKey]);
            $this->cleanUp($data, $dataGroupKey);
        }
        $this->checkUnknowDataGroup($data);

        return $this;
    }

    /**
     * @param array $data
     */
    private function checkUnknowDataGroup($data): void
    {
        if (empty($data)) {
            return;
        }

        $keys = array_keys($data);
        throw SegValidationException::forKey(array_shift($keys), 'Data-Group not allowed.', 7);
    }

    private function checkUnknowDatafields(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $key = current(array_keys($data));
        $value = current($data);

        throw SegValidationException::forKeyValue($key, $value, 'Data-Element not allowed.', 6);
    }

    private function cleanUp(array &$data, string $dataGroupKey, string $dataKey = null): void
    {
        if ($dataKey === null) {
            if (array_key_exists($dataGroupKey, $data)) {
                unset($data[$dataGroupKey]);
            }

            return;
        }

        if (array_key_exists($dataKey, $data[$dataGroupKey])) {
            unset($data[$dataGroupKey][$dataKey]);
        }
    }

    private function isDataIsAvailable(array $data, string $dataGroupKey, string $dataKey): bool
    {
        return ($data[$dataGroupKey][$dataKey] ?? '') !== '';
    }

    private function isDatafieldIsAvailable(array $data, string $dataGroupKey, string $dataKey): bool
    {
        return isset($data[$dataGroupKey][$dataKey]);
    }

    private function checkAvailability(array $data, string $dataGroupKey, string $dataKey): void
    {
        if ($this->isDatafieldIsAvailable($data, $dataGroupKey, $dataKey)) {
            return;
        }

        throw SegValidationException::forKey($dataKey, 'Data-Element not available, but needed.', 1);
    }

    private function isDatafieldOptional(?string $necessaryStatus): bool
    {
        return !($necessaryStatus === 'M' || $necessaryStatus === 'R');
    }

    private function checkStringType(?string $type, array $data, string $dataGroupKey, string $dataKey): void
    {
        $string = $data[$dataGroupKey][$dataKey] ?? '';

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

    private function checkStringLenght(string $lenght, array $data, string $dataGroupKey, string $dataKey): void
    {
        $string = $data[$dataGroupKey][$dataKey] ?? '';

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
