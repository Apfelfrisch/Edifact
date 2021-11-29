<?php

namespace Apfelfrisch\Edifact;

final class SeglineParser
{
    private const PLACE_HOLDER = '«>~<«';

    private UnaSegment $unaSegment;

    public function __construct(?UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
    }

    public function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment;
    }

    public function parseToBlueprint(string $segline, Elements $blueprint): Elements
    {
        $i = 0;
        $elements = new Elements;
        $dataArray = $this->explodeString($segline, $this->unaSegment->elementSeparator());

        foreach ($blueprint->toArray() as $BpDataKey => $BPelements) {
            $inputElement = [];
            if (isset($dataArray[$i])) {
                $inputElement = $this->explodeString($dataArray[$i], $this->unaSegment->componentSeparator());
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
        $segLineElements = $this->explodeString($segline, $this->unaSegment->elementSeparator());

        $elements = new Elements;

        $i = 0;
        foreach ($segLineElements as $element) {
            $components = $this->explodeString($element, $this->unaSegment->componentSeparator());

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

        if ($foundTermination = (bool)strpos($string, $this->unaSegment->escapeCharacter() . $pattern)) {
            $string = str_replace($this->unaSegment->escapeCharacter() . $pattern, self::PLACE_HOLDER, $string);
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
