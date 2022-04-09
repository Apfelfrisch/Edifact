<?php

namespace Apfelfrisch\Edifact\Segment;

final class SeglineParser
{
    private const SEG_UNKOWN_KEY_PREFIX = 'unknown';

    private UnaSegment $unaSegment;

    public function __construct(?UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
    }

    /** @deprecated */
    public function getUnaSegment(): UnaSegment
    {
        return $this->unaSegment;
    }

    public function parseToBlueprint(string $segline, Elements $blueprint): Elements
    {
        $blueprintArray = $blueprint->toArray();
        $elementKeys = array_keys($blueprintArray);

        $elementArray = $this->explodeString($this->removeContrlChars($segline), $this->unaSegment->elementSeparator());

        $elements = new Elements;

        foreach ($elementArray as $elementPosition => $elementsArray) {
            $components = $this->explodeString($elementsArray, $this->unaSegment->componentSeparator());

            if (null !== $elementKey = $elementKeys[$elementPosition] ?? null) {
                $componentKeys = array_keys($blueprintArray[$elementKey]);
                foreach ($components as $componentPosition => $value) {
                    if (null !== $componentKey = $componentKeys[$componentPosition] ?? null) {
                        $elements->addValue(
                            $elementKey,
                            $componentKey,
                            $this->prepareValue($value, $this->isNumeric($blueprintArray[$elementKey][$componentKey]))
                        );
                        continue;
                    }

                    $elements->addValue($elementKey, self::SEG_UNKOWN_KEY_PREFIX . "-$componentPosition", $this->prepareValue($value));
                }
                continue;
            }

            foreach ($components as $componentPosition => $value) {
                $elements->addValue(
                    self::SEG_UNKOWN_KEY_PREFIX . "-$elementPosition",
                    self::SEG_UNKOWN_KEY_PREFIX . "-$componentPosition",
                    $this->prepareValue($value)
                );
            }
        }

        return $elements;
    }

    public function parse(string $segline): Elements
    {
        $elementArray = $this->explodeString($this->removeContrlChars($segline), $this->unaSegment->elementSeparator());

        $elements = new Elements;

        $i = 0;
        foreach ($elementArray as $element) {
            $components = $this->explodeString($element, $this->unaSegment->componentSeparator());

            $j = 0;
            foreach ($components as $component) {
                $elements->addValue((string)$i, (string)$j, $this->prepareValue($component));
                $j++;
            }
            $i++;
        }

        return $elements;
    }

    private function removeContrlChars(string $segline): string
    {
        return str_replace(["\r", "\n"], '', $segline);
    }

    private function isNumeric(string|null $value): bool
    {
        return $value !== null && strpos($value, '|n|') !== false;
    }

    private function prepareValue(string $value, bool $isNumeric = false): string
    {
        if ($isNumeric && ! $this->unaSegment->usesPhpDecimalPoint()) {
            return str_replace($this->unaSegment->decimalPoint(), UnaSegment::PHP_DECIMAL, $value);
        }

        $value = str_replace(
            $this->unaSegment->escapeCharacter().$this->unaSegment->escapeCharacter(),
            $this->unaSegment->escapeCharacter(),
            $value
        );

        if (! $this->unaSegment->usesPhpSpaceCharacter()) {
            $value = str_replace($this->unaSegment->spaceCharacter(), UnaSegment::PHP_SPACE, $value);
        }

        return $value;
    }

    /**
     * @return array<int, string>
     */
    private function explodeString(string $string, string $pattern): array
    {
        if (str_contains($string, $this->unaSegment->escapeCharacter() . $pattern)) {
            return $this->safeExplodeString($string, $pattern);
        }

        return explode($pattern, $string);
    }

    /**
     * @return array<int, string>
     */
    private function safeExplodeString(string $string, string $pattern): array
    {
        $escapeChar = false;
        $partialString = '';
        $explodedString = [];

        foreach (mb_str_split($string) as $char) {
            if ($escapeChar === true) {
                if ($char !== $pattern) {
                    $partialString .= $this->unaSegment->escapeCharacter();
                }
                $partialString .= $char;
                $escapeChar = false;
                continue;
            }

            if ($char === $this->unaSegment->escapeCharacter()) {
                $escapeChar = true;
                continue;
            }

            if ($char === $pattern) {
                $explodedString[] = $partialString;
                $partialString = '';
                continue;
            }

            $partialString .= $char;
        }

        if ($partialString !== '') {
            $explodedString[] = $partialString;
        }

        return $explodedString;
    }
}
