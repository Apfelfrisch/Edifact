<?php 

namespace Proengeno\Edifact\Message;

use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;

abstract class Segment implements SegInterface 
{
    protected static $delimiter;
    protected static $validator;

    protected $elements;

    private $cache = [];

    protected function __construct(array $elements)
    {
        $this->elements = $elements;

        if (static::$delimiter === null) {
            static::setDelimiter(static::getDelimiter());
        }

        if (static::$validator === null) {
            static::setValidator(static::getValidator());
        }
    }

    public static function fromSegLine($segLine) 
    {
        return new static(static::mapToBlueprint($segLine));
    }

    public static function setDelimiter(Delimiter $delimiter = null)
    {
        static::$delimiter = $delimiter;
    }

    public static function getDelimiter()
    {
        if (static::$delimiter === null) {
            static::$delimiter = new Delimiter;
        }

        return static::$delimiter;
    }

    public static function setValidator(SegValidatorInterface $validator = null)
    {
        static::$validator = $validator;
    }

    public static function getValidator()
    {
        return static::$validator ?: new SegmentValidator;
    }

    public function name()
    {
        return array_values(array_values($this->elements)[0])[0];
    }

    public function validate()
    {
        static::$validator->validate(static::$validationBlueprint, $this->elements);
        
        return $this;
    }

    public function __toString()
    {
        if (!isset($this->cache['segLine'])) {
            $dataFields = array_map(function ($dataGroups) {
                return implode( static::$delimiter->getData(), static::$delimiter->terminate($this->deleteEmptyArrayEnds($dataGroups)) );
            }, $this->elements);

            $this->cache['segLine'] = implode(static::$delimiter->getDataGroup(), $this->deleteEmptyArrayEnds($dataFields));
        }
        
        return $this->cache['segLine'] . static::$delimiter->getSegment();
    }

    protected static function mapToBlueprint($segLine)
    {
        $inputDataGroups = static::getDelimiter()->explodeSegments($segLine);
        $i = 0;
        foreach (static::$validationBlueprint as $BpDataKey => $BPdataGroups) {
            if (isset($inputDataGroups[$i])) {
                $inputElement = static::$delimiter->explodeElements($inputDataGroups[$i]);
                $j = 0;
                foreach ($BPdataGroups as $key => $value) {
                    $elements[$BpDataKey][$key] = isset($inputElement[$j]) ? $inputElement[$j] : null;
                    $j++;
                }
            }
            $i++;
        }

        return @$elements ?: [];
    }

    private function deleteEmptyArrayEnds(array $array)
    {
        $reversed = array_reverse($array);
        foreach ($reversed as $key => $value) {
            if (!empty($value)) {
                break;
            }
            unset($reversed[$key]);
        }
        return array_reverse($reversed);
    }
}
