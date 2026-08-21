<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Segment;

use WeakMap;

final class SeglineParser
{
    private const string UNKNOWN_KEY_PREFIX = 'unknown';

    private readonly UnaSegment $unaSegment;

    /** @var non-empty-string */
    private readonly string $componentSeparator;

    /** @var non-empty-string */
    private readonly string $elementSeparator;

    /** @var non-empty-string */
    private readonly string $escapeCharacter;

    private readonly string $doubledEscapeCharacter;

    private readonly string $escapedElementSeparator;

    private readonly string $escapedComponentSeparator;

    private readonly bool $usesPhpDecimalPoint;

    private readonly bool $usesPhpSpaceCharacter;

    /**
     * Cached per-blueprint metadata, keyed by the blueprint instance:
     * the raw blueprint array (to detect mutation), stringified element
     * keys, component keys per element and numeric flags per component.
     *
     * @var WeakMap<Elements, array{
     *     array: array<array-key, array<array-key, string|null>>,
     *     elementKeys: list<string>,
     *     componentKeys: list<list<string>>,
     *     componentKeySlices: list<array<int, list<string>>>,
     *     numericFlags: list<list<bool>>,
     * }>
     */
    private WeakMap $blueprintCache;

    public function __construct(null|UnaSegment $unaSegment = null)
    {
        $this->unaSegment = $unaSegment ?? UnaSegment::getDefault();
        $this->componentSeparator = $this->unaSegment->componentSeparator();
        $this->elementSeparator = $this->unaSegment->elementSeparator();
        $this->escapeCharacter = $this->unaSegment->escapeCharacter();
        $this->doubledEscapeCharacter = $this->escapeCharacter . $this->escapeCharacter;
        $this->escapedElementSeparator = $this->escapeCharacter . $this->elementSeparator;
        $this->escapedComponentSeparator = $this->escapeCharacter . $this->componentSeparator;
        $this->usesPhpDecimalPoint = $this->unaSegment->usesPhpDecimalPoint();
        $this->usesPhpSpaceCharacter = $this->unaSegment->usesPhpSpaceCharacter();
        $this->blueprintCache = new WeakMap();
    }

    public function parseToBlueprint(string $segline, Elements $blueprint): Elements
    {
        [
            'elementKeys' => $elementKeys,
            'componentKeys' => $componentKeys,
            'componentKeySlices' => $componentKeySlices,
            'numericFlags' => $numericFlags,
        ] = $this->getBlueprintCache($blueprint);

        if (strpbrk($segline, "\r\n") !== false) {
            $segline = str_replace(["\r", "\n"], '', $segline);
        }

        // Without an escape character in the whole segline, no separator can
        // be escaped and (with a default space character) no value needs
        // any preparation.
        $hasEscapeCharacter = str_contains($segline, $this->escapeCharacter);
        $skipValuePreparation = !$hasEscapeCharacter && $this->usesPhpSpaceCharacter;
        $skipNumericValuePreparation = $skipValuePreparation && $this->usesPhpDecimalPoint;

        $elementArray = $hasEscapeCharacter
            ? $this->explodeString($segline, $this->elementSeparator, $this->escapedElementSeparator)
            : explode($this->elementSeparator, $segline);

        $result = [];

        foreach ($elementArray as $elementPosition => $elementString) {
            $components = $hasEscapeCharacter
                ? $this->explodeString($elementString, $this->componentSeparator, $this->escapedComponentSeparator)
                : explode($this->componentSeparator, $elementString);

            if (null === ($elementKey = $elementKeys[$elementPosition] ?? null)) {
                $result[self::UNKNOWN_KEY_PREFIX . "-{$elementPosition}"] = $this->parseUnknownComponents(
                    $components,
                    $skipValuePreparation,
                );

                continue;
            }

            // Fast path: values need no preparation and every component has a
            // blueprint key, so the whole element assembles in one call.
            if (
                $skipNumericValuePreparation
                && null !== ($keySlice = $componentKeySlices[$elementPosition][count($components)] ?? null)
            ) {
                $result[$elementKey] = array_combine($keySlice, $components);

                continue;
            }

            $result[$elementKey] = $this->parseComponentsToBlueprint(
                $components,
                $componentKeys[$elementPosition],
                $numericFlags[$elementPosition],
                $skipValuePreparation,
                $skipNumericValuePreparation,
            );
        }

        return new Elements($result);
    }

    /**
     * @param list<string> $components
     *
     * @return array<string, string>
     */
    // @mago-expect lint:no-boolean-flag-parameter — private helper on the parser hot path
    private function parseUnknownComponents(array $components, bool $skipValuePreparation): array
    {
        $result = [];

        foreach ($components as $componentPosition => $value) {
            $result[self::UNKNOWN_KEY_PREFIX . "-{$componentPosition}"] = $skipValuePreparation
                ? $value
                : $this->prepareValue($value);
        }

        return $result;
    }

    /**
     * @param list<string> $components
     * @param list<string> $componentKeys
     * @param list<bool> $numericFlags
     *
     * @return array<string, string>
     */
    // @mago-expect lint:no-boolean-flag-parameter — private helper on the parser hot path
    private function parseComponentsToBlueprint(
        array $components,
        array $componentKeys,
        array $numericFlags,
        bool $skipValuePreparation,
        bool $skipNumericValuePreparation,
    ): array {
        $result = [];

        foreach ($components as $componentPosition => $value) {
            if (null === ($componentKey = $componentKeys[$componentPosition] ?? null)) {
                $result[self::UNKNOWN_KEY_PREFIX . "-{$componentPosition}"] = $skipValuePreparation
                    ? $value
                    : $this->prepareValue($value);

                continue;
            }

            if ($numericFlags[$componentPosition]) {
                $result[$componentKey] = $skipNumericValuePreparation ? $value : $this->prepareNumericValue($value);

                continue;
            }

            $result[$componentKey] = $skipValuePreparation ? $value : $this->prepareValue($value);
        }

        return $result;
    }

    public function parse(string $segline): Elements
    {
        if (strpbrk($segline, "\r\n") !== false) {
            $segline = str_replace(["\r", "\n"], '', $segline);
        }

        $hasEscapeCharacter = str_contains($segline, $this->escapeCharacter);
        $skipValuePreparation = !$hasEscapeCharacter && $this->usesPhpSpaceCharacter;

        $elementArray = $hasEscapeCharacter
            ? $this->explodeString($segline, $this->elementSeparator, $this->escapedElementSeparator)
            : explode($this->elementSeparator, $segline);

        $result = [];

        foreach ($elementArray as $elementPosition => $element) {
            $components = $hasEscapeCharacter
                ? $this->explodeString($element, $this->componentSeparator, $this->escapedComponentSeparator)
                : explode($this->componentSeparator, $element);

            if ($skipValuePreparation) {
                $result[$elementPosition] = $components;

                continue;
            }

            foreach ($components as $componentPosition => $component) {
                $result[$elementPosition][$componentPosition] = $this->prepareValue($component);
            }
        }

        return new Elements($result);
    }

    /**
     * @return array{
     *     array: array<array-key, array<array-key, string|null>>,
     *     elementKeys: list<string>,
     *     componentKeys: list<list<string>>,
     *     componentKeySlices: list<array<int, list<string>>>,
     *     numericFlags: list<list<bool>>,
     * }
     */
    private function getBlueprintCache(Elements $blueprint): array
    {
        $blueprintArray = $blueprint->toArray();

        $cache = $this->blueprintCache[$blueprint] ?? null;

        // Comparing against the cached raw array is O(1) while the blueprint
        // stays untouched and detects mutation after the first parse.
        if ($cache !== null && $cache['array'] === $blueprintArray) {
            return $cache;
        }

        $elementKeys = [];
        $componentKeys = [];
        $componentKeySlices = [];
        $numericFlags = [];

        foreach ($blueprintArray as $elementKey => $components) {
            $elementKeys[] = (string) $elementKey;

            $keys = [];
            $flags = [];

            foreach ($components as $componentKey => $value) {
                $keys[] = (string) $componentKey;
                $flags[] = $value !== null && str_contains($value, '|n|');
            }

            $slices = [];

            for ($length = 1, $keyCount = count($keys); $length <= $keyCount; $length++) {
                $slices[$length] = array_slice($keys, 0, $length);
            }

            $componentKeys[] = $keys;
            $componentKeySlices[] = $slices;
            $numericFlags[] = $flags;
        }

        return $this->blueprintCache[$blueprint] = [
            'array' => $blueprintArray,
            'elementKeys' => $elementKeys,
            'componentKeys' => $componentKeys,
            'componentKeySlices' => $componentKeySlices,
            'numericFlags' => $numericFlags,
        ];
    }

    private function prepareNumericValue(string $value): string
    {
        if ($this->usesPhpDecimalPoint) {
            return $this->prepareValue($value);
        }

        return str_replace($this->unaSegment->decimalPoint(), UnaSegment::PHP_DECIMAL, $value);
    }

    private function prepareValue(string $value): string
    {
        $value = str_replace($this->doubledEscapeCharacter, $this->escapeCharacter, $value);

        if (!$this->usesPhpSpaceCharacter) {
            $value = str_replace($this->unaSegment->spaceCharacter(), UnaSegment::PHP_SPACE, $value);
        }

        return $value;
    }

    /**
     * @param non-empty-string $pattern
     *
     * @return list<string>
     */
    private function explodeString(string $string, string $pattern, string $escapedPattern): array
    {
        if (str_contains($string, $escapedPattern)) {
            return $this->safeExplodeString($string, $pattern);
        }

        return explode($pattern, $string);
    }

    /**
     * Explode on the pattern, then re-merge parts whose boundary separator
     * was escaped (an odd number of escape characters before the pattern).
     *
     * @param non-empty-string $pattern
     *
     * @return list<string>
     */
    private function safeExplodeString(string $string, string $pattern): array
    {
        $explodedString = [];
        $mergedPart = null;

        foreach (explode($pattern, $string) as $part) {
            if ($mergedPart === null) {
                $mergedPart = $part;

                continue;
            }

            if ($this->endsWithActiveEscape($mergedPart)) {
                // Drop the escape character and restore the escaped pattern
                $mergedPart = substr($mergedPart, 0, -strlen($this->escapeCharacter)) . $pattern . $part;

                continue;
            }

            $explodedString[] = $mergedPart;
            $mergedPart = $part;
        }

        if ($mergedPart !== null) {
            // A dangling escape character at the end of the string is dropped
            if ($this->endsWithActiveEscape($mergedPart)) {
                $mergedPart = substr($mergedPart, 0, -strlen($this->escapeCharacter));
            }

            if ($mergedPart !== '') {
                $explodedString[] = $mergedPart;
            }
        }

        return $explodedString;
    }

    private function endsWithActiveEscape(string $string): bool
    {
        $escapeLength = strlen($this->escapeCharacter);
        $escapeCount = 0;
        $offset = strlen($string);

        while (
            $offset >= $escapeLength
            && substr_compare($string, $this->escapeCharacter, $offset - $escapeLength, $escapeLength) === 0
        ) {
            $escapeCount++;
            $offset -= $escapeLength;
        }

        return ($escapeCount % 2) === 1;
    }
}
