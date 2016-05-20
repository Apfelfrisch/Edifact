<?php 

namespace Proengeno\Edifact\Validation;

use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\SegValidationException;

class SegmentValidator implements SegValidatorInterface 
{
    const ALPHA = 'a';
    const NUMERIC = 'n';
    const ALPHA_NUMERIC = 'an';

    public function validate($blueprint, $data)
    {
        foreach ($blueprint as $dataGroupKey => $dataGroup) {
            foreach ($dataGroup as $dataKey => $validation) {
                if ($validation !== null) {
                    list($necessaryStatus, $type, $lenght) = explode('|', $validation);

                    if ($this->isDatafieldOptional($necessaryStatus) && !$this->isDatafieldIsAvailable($data, $dataGroupKey, $dataKey)) {
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
    
    private function checkUnknowDataGroup($data)
    {
        if (empty($data)) {
            return;
        }

        $keys = array_keys($data);
        throw SegValidationException::forKey(array_shift($keys), 'Datengruppe nicht erlaubt', 7);
    }

    private function checkUnknowDatafields($data)
    {
        if (empty($data)) {
            return;
        }
        $key = current(array_keys($data));
        $value = current($data);

        throw SegValidationException::forKeyValue($key, $value, 'Daten-Element nicht erlaubt', 6);
    }

    private function cleanUp(&$data, $dataGroupKey, $dataKey = null)
    {
        if ($dataKey) {
            unset($data[$dataGroupKey][$dataKey]);
        } else {
            unset($data[$dataGroupKey]);
        }
    }
    
    private function isDatafieldIsAvailable($data, $dataGroupKey, $dataKey)
    {
        return isset($data[$dataGroupKey][$dataKey]);
    }

    private function checkAvailability($data, $dataGroupKey, $dataKey)
    {
        if ($this->isDatafieldIsAvailable($data, $dataGroupKey, $dataKey)) {
            return;
        }

        throw SegValidationException::forKey($dataKey, 'Daten-Element nicht enthalten, aber benötigt.', 1);
    }

    private function isDatafieldOptional($necessaryStatus)
    {
        return ! ($necessaryStatus == 'M' || $necessaryStatus == 'R');
    }

    private function checkStringType($type, $data, $dataGroupKey, $dataKey)
    {
        $string = $data[$dataGroupKey][$dataKey];

        if ($type == static::ALPHA_NUMERIC || $type == null) {
            return;
        }
        if ($type == static::NUMERIC && !is_numeric($string)) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Daten-Element enthält nicht numerische Zeichen.', 2);
        }
        if ($type == static::ALPHA && !ctype_alpha(str_replace(' ', '', $string))) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Daten-Element darf nur ausschließlich Buchstaben enthalten.', 3);
        }
    }
    
    private function checkStringLenght($lenght, $data, $dataGroupKey, $dataKey)
    {
        $string = $data[$dataGroupKey][$dataKey];

        $strLen = strlen($string);
        if ($strLen == 0 ) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Daten-Element fehlt oder ist leer.', 4);
        }
        if ($lenght < $strLen) {
            throw SegValidationException::forKeyValue($dataKey, $string, 'Daten-Element ist länger als ' . $lenght . ' Zeichen.', 5);
        }
    }
}
