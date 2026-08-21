<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

final class SeglineParser
{
    private const string UNKNOWN_KEY_PREFIX = 'unknown';

    private readonly UnaSegment $unaSegment;

    public function __construct(null|UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
    }

    public function parseToBlueprint(string $segline, Elements $blueprint): Elements
    {
        $blueprintArray = $blueprint->toArray();
        $elementKeys = array_keys($blueprintArray);

        $elementArray = $this->explodeString(
            $this->removeControlChars($segline),
            $this->unaSegment->elementSeparator(),
        );

        $elements = new Elements();

        foreach ($elementArray as $elementPosition => $elementString) {
            $components = $this->explodeString($elementString, $this->unaSegment->componentSeparator());

            if (null === ($elementKey = $elementKeys[$elementPosition] ?? null)) {
                foreach ($components as $componentPosition => $value) {
                    $elements->addValue(
                        self::UNKNOWN_KEY_PREFIX . "-{$elementPosition}",
                        self::UNKNOWN_KEY_PREFIX . "-{$componentPosition}",
                        $this->prepareValue($value),
                    );
                }

                continue;
            }

            $componentKeys = array_keys($blueprintArray[$elementKey]);

            foreach ($components as $componentPosition => $value) {
                if (null === ($componentKey = $componentKeys[$componentPosition] ?? null)) {
                    $elements->addValue(
                        (string) $elementKey,
                        self::UNKNOWN_KEY_PREFIX . "-{$componentPosition}",
                        $this->prepareValue($value),
                    );

                    continue;
                }

                $elements->addValue(
                    (string) $elementKey,
                    (string) $componentKey,
                    $this->isNumeric($blueprintArray[$elementKey][$componentKey])
                        ? $this->prepareNumericValue($value)
                        : $this->prepareValue($value),
                );
            }
        }

        return $elements;
    }

    public function parse(string $segline): Elements
    {
        $elementArray = $this->explodeString(
            $this->removeControlChars($segline),
            $this->unaSegment->elementSeparator(),
        );

        $elements = new Elements();

        foreach (array_values($elementArray) as $elementPosition => $element) {
            $components = $this->explodeString($element, $this->unaSegment->componentSeparator());

            foreach (array_values($components) as $componentPosition => $component) {
                $elements->addValue(
                    (string) $elementPosition,
                    (string) $componentPosition,
                    $this->prepareValue($component),
                );
            }
        }

        return $elements;
    }

    private function removeControlChars(string $segline): string
    {
        return str_replace(["\r", "\n"], '', $segline);
    }

    private function isNumeric(null|string $value): bool
    {
        return $value !== null && str_contains($value, '|n|');
    }

    private function prepareNumericValue(string $value): string
    {
        if ($this->unaSegment->usesPhpDecimalPoint()) {
            return $this->prepareValue($value);
        }

        return str_replace($this->unaSegment->decimalPoint(), UnaSegment::PHP_DECIMAL, $value);
    }

    private function prepareValue(string $value): string
    {
        $value = str_replace(
            $this->unaSegment->escapeCharacter() . $this->unaSegment->escapeCharacter(),
            $this->unaSegment->escapeCharacter(),
            $value,
        );

        if (!$this->unaSegment->usesPhpSpaceCharacter()) {
            $value = str_replace($this->unaSegment->spaceCharacter(), UnaSegment::PHP_SPACE, $value);
        }

        return $value;
    }

    /**
     * @param non-empty-string $pattern
     *
     * @return list<string>
     */
    private function explodeString(string $string, string $pattern): array
    {
        if (str_contains($string, $this->unaSegment->escapeCharacter() . $pattern)) {
            return $this->safeExplodeString($string, $pattern);
        }

        return explode($pattern, $string);
    }

    /**
     * @return list<string>
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
