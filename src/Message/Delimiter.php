<?php 

namespace Proengeno\Edifact\Message;

class Delimiter 
{
    private $data;
    private $dataGroup;
    private $decimal;
    private $terminator;
    private $empty;
    private $segment;
    
    public function __construct($data = ':', $dataGroup = '+', $decimal = '.', $terminator = '?', $empty = ' ', $segment = '\'')
    {
        $this->data = $data;
        $this->dataGroup = $dataGroup;
        $this->decimal = $decimal;
        $this->terminator = $terminator;
        $this->empty = $empty;
        $this->segment = $segment;
    }
    
    public static function setFromEdifact($string)
    {
        if (self::stringHasUna($string)) {
            list($data, $dataGroup, $decimal, $terminator, $empty, $segment) = str_split(substr($string, 3, 6));

            return new self($data, $dataGroup, $decimal, $terminator, $empty, $segment);
        }

        return new self;
    }

    public function terminate($string)
    {
        return str_replace(
            [$this->data, $this->dataGroup, '\\n'], 
            [$this->terminator . $this->data, $this->terminator . $this->dataGroup, ''],
            $string
        );
    }

    public function explodeSegments($string)
    {
        return $this->explodeString($string, $this->dataGroup);
    }
    
    public function explodeElements($string)
    {
        return $this->explodeString($string, $this->data);
    }
    
    public function getData()
    {
        return $this->data;
    }

    public function getDataGroup()
    {
        return $this->dataGroup;
    }

    public function getDecimal()
    {
        return $this->decimal;
    }

    public function getTerminator()
    {
        return $this->terminator;
    }

    public function getEmpty()
    {
        return $this->empty;
    }

    public function getSegment()
    {
        return $this->segment;
    }

    private function explodeString($string, $pattern)
    {
        $string = $this->removeLineBreaks($string);

        $foundTermination = false;
        if (strpos($string, $this->terminator.$pattern) ) {
            $string = str_replace($this->terminator.$pattern, ' #-placeHolder-# ', $string);

            $foundTermination = true;
        }

        $explodedString = explode($pattern, $string);

        if ($foundTermination) {
            for ($i = 0; $i < count($explodedString); $i++) {
                $explodedString[$i] = str_replace(' #-placeHolder-# ', $pattern, $explodedString[$i]);
            }
        }

        return $this->trimLastItem($explodedString);
    }

    private function trimLastItem(array $array)
    {
        if (end($array) == '') {
            array_pop($array);
        }

        return $array;
    }

    private function removeLineBreaks($string)
    {
        return str_replace(["\r", "\n"], '', $string);
    }

    private static function stringHasUna($string)
    {
        return substr($string, 0, 3) == 'UNA';
    }
}
