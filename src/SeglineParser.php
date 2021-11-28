<?php

namespace Apfelfrisch\Edifact;

final class SeglineParser
{
    private const PLACE_HOLDER = '«>~<«';

    private Delimiter $delimiter;

    public function __construct(?Delimiter $delimiter = null)
    {
        $this->delimiter = $delimiter ?? Delimiter::getDefault();
    }

    public function getDelimiter(): Delimiter
    {
        return $this->delimiter;
    }

    public function parseToBlueprint(string $segline, Elements $blueprint): Elements
    {
        $i = 0;
        $elements = new Elements;
        $dataArray = $this->explodeString($segline, $this->delimiter->getElementSeparator());

        foreach ($blueprint->toArray() as $BpDataKey => $BPelements) {
            $inputElement = [];
            if (isset($dataArray[$i])) {
                $inputElement = $this->explodeString($dataArray[$i], $this->delimiter->getComponentSeparator());
            }

            $j = 0;
            foreach (array_keys($BPelements) as $key) {
                $elements->addValue($BpDataKey, $key, isset($inputElement[$j]) ? $inputElement[$j] : null);
                $j++;
            }
            $i++;
        }

        return $elements;
    }

    public function parse(string $segline): Elements
    {
        $segLineElements = $this->explodeString($segline, $this->delimiter->getElementSeparator());

        $elements = new Elements;

        $i = 0;
        foreach ($segLineElements as $element) {
            $components = $this->explodeString($element, $this->delimiter->getComponentSeparator());

            $j = 0;
            foreach ($components as $component) {
                $elements->addValue((string)$i, (string)$j, $component);
                $j++;
            }
            $i++;
        }

        return $elements;
    }

    /**
     * @return list<string>
     */
    private function explodeString(string $string, string $pattern): array
    {
        $string = str_replace(["\r", "\n"], '', $string);

        if ($foundTermination = (bool)strpos($string, $this->delimiter->getEscapeCharacter() . $pattern)) {
            $string = str_replace($this->delimiter->getEscapeCharacter() . $pattern, self::PLACE_HOLDER, $string);
        }

        $explodedString = explode($pattern, $string);

        if ($foundTermination) {
            $explodedString = array_map(
                fn(string $string): string => str_replace(self::PLACE_HOLDER, $pattern, $string),
                $explodedString
            );
        }

        return $this->trimLastItem($explodedString);
    }

    /**
     * @param list<string> $array
     *
     * @return list<string>
     */
    private function trimLastItem(array $array): array
    {
        if (end($array) == '') {
            array_pop($array);
        }

        return $array;
    }
}
