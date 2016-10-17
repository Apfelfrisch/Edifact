<?php

namespace Proengeno\Edifact\Templates;

use Proengeno\Edifact\Message\Delimiter;
use Proengeno\Edifact\Interfaces\SegInterface;
use Proengeno\Edifact\Message\SegmentDescription;
use Proengeno\Edifact\Validation\SegmentValidator;
use Proengeno\Edifact\Interfaces\SegValidatorInterface;
use Proengeno\Edifact\Exceptions\EdifactException;

abstract class AbstractSegment implements SegInterface
{
    protected static $jsonDescribtion = null;
    protected static $buildValidator;
    protected static $buildDelimiter;
    protected $elements = [];
    protected $cache = [];
    protected $delimiter;
    protected $validator;

    protected function __construct(array $elements)
    {
        $this->elements = $elements;
        $this->delimiter = static::getBuildDelimiter();
        $this->validator = static::$buildValidator ?: new SegmentValidator;
    }

    public static function fromSegLine($segLine)
    {
        return new static(static::mapToBlueprint($segLine));
    }

    public static function setBuildDelimiter(Delimiter $buildDelimiter = null)
    {
        self::$buildDelimiter = $buildDelimiter;
    }

    public static function getBuildDelimiter()
    {
        return self::$buildDelimiter ?: new Delimiter;
    }

    public static function setBuildValidator(SegValidatorInterface $buildValidator = null)
    {
        self::$buildValidator = $buildValidator;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function name()
    {
        return array_values(array_values($this->elements)[0])[0];
    }

    public function validate()
    {
        $this->validator->validate(static::$validationBlueprint, $this->elements);

        return $this;
    }

    public function __toString()
    {
        if (!isset($this->cache['segLine'])) {
            $dataFields = array_map(function($dataGroups) {
                return implode($this->delimiter->getData(), $this->delimiter->terminate($this->deleteEmptyArrayEnds($dataGroups)));
            }, $this->elements);

            $this->cache['segLine'] = implode($this->delimiter->getDataGroup(), $this->deleteEmptyArrayEnds($dataFields));
        }

        return $this->cache['segLine'] . $this->delimiter->getSegment();
    }

    public function __call($name, $arguments)
    {
        $selfMethod = substr($name, 0, -4);

        if (! method_exists(static::class, $selfMethod)) {
            throw new EdifactException("Unkow Method '$name'.");
        }
        if (count($arguments) != 1) {
            throw new EdifactException("Metacall requiere exact 1 Argument.");
        }

        $metaMethod = $arguments[0];

        return static::meta()->$metaMethod($selfMethod, $this->$selfMethod());
    }

    public static function meta()
    {
        return SegmentDescription::make(static::$jsonDescribtion);
    }

    protected static function mapToBlueprint($segLine)
    {
        $inputDataGroups = static::getBuildDelimiter()->explodeSegments($segLine);
        $elements = [];
        $i = 0;
        foreach (static::$validationBlueprint as $BpDataKey => $BPdataGroups) {
            if (isset($inputDataGroups[$i])) {
                $inputElement = static::getBuildDelimiter()->explodeElements($inputDataGroups[$i]);
                $j = 0;
                foreach ($BPdataGroups as $key => $value) {
                    $elements[$BpDataKey][$key] = isset($inputElement[$j]) ? $inputElement[$j] : null;
                    $j++;
                }
            }
            $i++;
        }

        return $elements;
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
