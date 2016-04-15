<?php 

namespace Proengeno\Edifact\Message\Segments;

use DateTime;
use Proengeno\Edifact\Exceptions\SegValidationException;

class Dtm extends SegFramework 
{
    private static $timecodes = [
        102 => 'YmdH',
        203 => 'YmdHi',
        610 => 'YmdH',
    ];

    protected static $validationBlueprint = [
        'DTM' => ['DTM' => 'M|a|3'],
        'C507' => ['2005' => 'M|an|3', '2380' => 'M|an|35', '2379' => 'M|an|3'],
    ];

    public static function fromAttributes($qualifier, DateTime $date, $code)
    {
        return new static([
            'DTM' => ['DTM' => 'DTM'],
            'C507' => ['2005' => $qualifier, '2380' => $date->format(static::getTimecodeFormat($code)), '2379' => $code],
        ]);
    }

    public static function getTimecodeFormat($code)
    {
        if (isset(static::$timecodes[$code])) {
            return static::$timecodes[$code];
        }

        throw SegValidationException::forKeyValue('DTM', $code, "Zeitcode ist unbekannt.");
    }

    public function qualifier()
    {
        return @$this->elements['C507']['2005'] ?: null;
    }

    public function date()
    {
        return DateTime::createFromFormat(static::$timecodes[$this->code()], $this->elements['C507']['2380']);
    }

    public function code()
    {
        return @$this->elements['C507']['2379'] ?: null;
    }
}
