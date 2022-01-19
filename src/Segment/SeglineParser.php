<?php

namespace Apfelfrisch\Edifact\Segment;

final class SeglineParser
{
    private const SEG_UNKOWN_KEY_PREFIX = 'unknown';

    private const PLACE_HOLDER = '>§<';

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
        $elements = new Elements;

        $blueprintArray = $blueprint->toArray();
        $elementKeys = array_keys($blueprintArray);
        foreach ($this->explodeString($segline, $this->unaSegment->elementSeparator()) as $elementPosition => $elementsArray) {
            $components = $this->explodeString($elementsArray, $this->unaSegment->componentSeparator());

            if (null !== $elementKey = $elementKeys[$elementPosition] ?? null) {
                $componentKeys = array_keys($blueprintArray[$elementKey]);
                foreach ($components as $componentPosition => $value) {
                    if (null !== $componentKey = $componentKeys[$componentPosition] ?? null) {
                        $elements->addValue($elementKey, $componentKey, $value);
                        continue;
                    }

                    $elements->addValue($elementKey, self::SEG_UNKOWN_KEY_PREFIX . "-$componentPosition", $value);
                }
                continue;
            }

            foreach ($components as $componentPosition => $value) {
                $elements->addValue(self::SEG_UNKOWN_KEY_PREFIX . "-$elementPosition", self::SEG_UNKOWN_KEY_PREFIX . "-$componentPosition", $value);
            }
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
     * @return array<int, string>
     */
    private function explodeString(string $string, string $pattern): array
    {
        $string = str_replace(["\r", "\n"], '', $string);

        if (str_contains($string, $this->unaSegment->escapeCharacter() . $pattern)) {
            return $this->safeExplodeString($string, $pattern);
        }

        return explode($pattern, $string);
    }

    private function safeExplodeString(string $string, string $pattern): array
    {
        $escapeChar = false;
        $array = [];
        $partialString = '';
        foreach (mb_str_split($string) as $char) {
            if ($escapeChar === true) {
                if ($char === $pattern) {
                    $partialString .= $char;
                } else {
                    $partialString .= $this->unaSegment->escapeCharacter() . $char;
                }
                $escapeChar = false;
                continue;
            }

            if ($char === $this->unaSegment->escapeCharacter()) {
                $escapeChar = true;
                continue;
            }

            if ($char === $pattern) {
                $array[] = $partialString;
                $partialString = '';
                continue;
            }

            $partialString .= $char;
        }

        if ($partialString !== '') {
            $array[] = $partialString;
        }

        return $array;
    }
}
