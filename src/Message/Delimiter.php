<?php

namespace Proengeno\Edifact\Message;

class Delimiter
{
    const UNA_SEGMENT = 'UNA';
    const PLACE_HOLDER = '«>~<«';

    public function __construct(
        private string $data = ':',
        private string $dataGroup = '+',
        private string $decimal = '.',
        private string $terminator = '?',
        private string $empty = ' ',
        private string $segment = '\''
    ) { }

    /**
     * @return self
     */
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

    /**
     * @param string|array $string
     *
     * @return string|array
     */
    public function terminate($string)
    {
        return str_replace(
            [$this->data, $this->dataGroup, '\\n'],
            [$this->terminator . $this->data, $this->terminator . $this->dataGroup, ''],
            $string
        );
    }

    /**
     * @param string $string
     *
     * @return list
     */
    public function explodeSegments($string)
    {
        return $this->explodeString($string, $this->dataGroup);
    }

    /**
     * @param string $string
     *
     * @return list
     */
    public function explodeElements($string)
    {
        return $this->explodeString($string, $this->data);
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getDataGroup()
    {
        return $this->dataGroup;
    }

    /**
     * @return string
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * @return string
     */
    public function getTerminator()
    {
        return $this->terminator;
    }

    /**
     * @return string
     */
    public function getEmpty()
    {
        return $this->empty;
    }

    /**
     * @return string
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * @return list
     */
    private function explodeString(string $string, string $pattern): array
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

    /**
     * @param list $array
     *
     * @return list
     */
    private function trimLastItem(array $array): array
    {
        if (end($array) == '') {
            array_pop($array);
        }

        return $array;
    }

    private function removeLineBreaks(string $string): string
    {
        return str_replace(["\r", "\n"], '', $string);
    }
}
