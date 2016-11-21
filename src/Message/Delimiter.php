<?php

namespace Proengeno\Edifact\Message;

class Delimiter
{
    const UNA_SEGMENT = 'UNA';
    const PLACE_HOLDER = '«>~<«';

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

    public static function setFromFile(EdifactFile $file)
    {
        $position = $file->tell();
        $file->rewind();

        if ($file->read(3) != self::UNA_SEGMENT) {
            $instance = new self();
        } else {
            $instance = new self(
                $file->getChar(), $file->getChar(), $file->getChar(), $file->getChar(), $file->getChar(), $file->getChar()
            );
        }
        $file->seek($position);

        return $instance;
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

        if ($foundTermination = (boolean)strpos($string, $this->terminator . $pattern)) {
            $string = str_replace($this->terminator . $pattern, self::PLACE_HOLDER, $string);
        }

        $explodedString = explode($pattern, $string);

        if ($foundTermination) {
            for ($i = 0, $count = count($explodedString); $i < $count; $i++) {
                $explodedString[$i] = str_replace(self::PLACE_HOLDER, $pattern, $explodedString[$i]);
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
}
