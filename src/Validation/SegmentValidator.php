<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Elements;
use Iterator;
use WeakMap;

final class SegmentValidator
{
    private const array MESSAGES = [
        Failure::UNKNOWN_ELEMENT => 'The input Element is unknown',
        Failure::UNKNOWN_COMPONENT => 'The input Component is unknown',
    ];

    private readonly ValueValidator $valueValidator;

    /**
     * Cached per-blueprint metadata, keyed by the blueprint instance:
     * the raw blueprint array (to detect mutation), stringified element
     * keys and component keys per element key.
     *
     * @var WeakMap<Elements, array{
     *     array: array<array-key, array<array-key, string|null>>,
     *     elementKeys: list<string>,
     *     componentKeys: array<string, list<string>>,
     * }>
     */
    private WeakMap $blueprintCache;

    private string $segmentName = '';

    private int $elementPosition = -1;

    private int $componentPosition = -1;

    public function __construct()
    {
        $this->valueValidator = new ValueValidator();
        $this->blueprintCache = new WeakMap();
    }

    /** @return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        $this->segmentName = $data->getName();
        $this->elementPosition = -1;

        [
            'array' => $blueprintArray,
            'elementKeys' => $blueprintElementKeys,
            'componentKeys' => $blueprintComponentKeys,
        ] = $this->getBlueprintCache($blueprint);

        foreach ($data->toArray() as $elementKey => $components) {
            $elementKey = (string) $elementKey;
            $this->elementPosition++;
            $this->componentPosition = -1;

            $blueprintElement = $blueprintArray[$elementKey] ?? [];
            $elementIsUnknown = $blueprintElement === [];

            foreach ($components as $componentKey => $value) {
                $componentKey = (string) $componentKey;
                $this->componentPosition++;

                if ($elementIsUnknown) {
                    yield new Failure(
                        Failure::UNKNOWN_ELEMENT,
                        $this->segmentName,
                        $this->elementPosition,
                        $this->componentPosition,
                        $value,
                        $this->buildMessage(Failure::UNKNOWN_ELEMENT),
                    );

                    continue;
                }

                if (!array_key_exists($componentKey, $blueprintElement)) {
                    yield new Failure(
                        Failure::UNKNOWN_COMPONENT,
                        $this->segmentName,
                        $this->elementPosition,
                        $this->componentPosition,
                        $value,
                        $this->buildMessage(Failure::UNKNOWN_COMPONENT),
                    );
                }

                if (null !== ($rules = $blueprintElement[$componentKey] ?? null)) {
                    yield from $this->validateValue($value, $rules, $elementKey, $componentKey);
                }
            }

            $blueprintElementKey = $blueprintElementKeys[$this->elementPosition] ?? null;

            if (
                $blueprintElementKey !== null
                && ($this->componentPosition + 1) < count($blueprintComponentKeys[$blueprintElementKey])
            ) {
                yield from $this->validateMissingComponents($blueprintElementKeys, $blueprintComponentKeys, $blueprint);
            }
        }

        if (($this->elementPosition + 1) < count($blueprintElementKeys)) {
            yield from $this->validateMissingElements($blueprintElementKeys, $blueprintComponentKeys, $blueprint);
        }
    }

    /**
     * @return array{
     *     array: array<array-key, array<array-key, string|null>>,
     *     elementKeys: list<string>,
     *     componentKeys: array<string, list<string>>,
     * }
     */
    private function getBlueprintCache(Elements $blueprint): array
    {
        $blueprintArray = $blueprint->toArray();

        $cache = $this->blueprintCache[$blueprint] ?? null;

        // Comparing against the cached raw array is O(1) while the blueprint
        // stays untouched and detects mutation after the first validation.
        if ($cache !== null && $cache['array'] === $blueprintArray) {
            return $cache;
        }

        $elementKeys = [];
        $componentKeys = [];

        foreach ($blueprintArray as $elementKey => $components) {
            $elementKey = (string) $elementKey;
            $elementKeys[] = $elementKey;
            $componentKeys[$elementKey] = array_map(strval(...), array_keys($components));
        }

        return $this->blueprintCache[$blueprint] = [
            'array' => $blueprintArray,
            'elementKeys' => $elementKeys,
            'componentKeys' => $componentKeys,
        ];
    }

    /**
     * @return list<Failure>
     */
    private function validateValue(null|string $value, string $rules, string $elementKey, string $componentKey): array
    {
        $failures = $this->valueValidator->validate($value, $rules, (string) $this->elementPosition);

        $result = [];

        foreach ($failures as $failureType => $message) {
            $message = match ($failureType) {
                Failure::MISSING_COMPONENT => str_replace('%', "{$elementKey}:{$componentKey}", $message),
                default => $message,
            };

            $result[] = new Failure(
                $failureType,
                $this->segmentName,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $message,
            );
        }

        return $result;
    }

    /**
     * @param list<string> $blueprintElementKeys
     * @param array<string, list<string>> $blueprintComponentKeys
     *
     * @return list<Failure>
     */
    private function validateMissingComponents(
        array $blueprintElementKeys,
        array $blueprintComponentKeys,
        Elements $blueprint,
    ): array {
        if (null === ($blueprintElementKey = $blueprintElementKeys[$this->elementPosition] ?? null)) {
            return [];
        }

        $componentKeys = $blueprintComponentKeys[$blueprintElementKey];
        $blueprintComponentPosition = $this->componentPosition + 1;

        $failures = [];

        while (null !== ($blueprintComponentKey = $componentKeys[$blueprintComponentPosition] ?? null)) {
            $blueprintComponentPosition++;

            if (null === ($rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey))) {
                continue;
            }

            $failures = [
                ...$failures,
                ...$this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey),
            ];
        }

        return $failures;
    }

    /**
     * @param list<string> $blueprintElementKeys
     * @param array<string, list<string>> $blueprintComponentKeys
     *
     * @return list<Failure>
     */
    private function validateMissingElements(
        array $blueprintElementKeys,
        array $blueprintComponentKeys,
        Elements $blueprint,
    ): array {
        $blueprintElementPosition = $this->elementPosition + 1;

        $failures = [];

        while (null !== ($blueprintElementKey = $blueprintElementKeys[$blueprintElementPosition] ?? null)) {
            $blueprintElementPosition++;

            foreach ($blueprintComponentKeys[$blueprintElementKey] as $blueprintComponentKey) {
                if (null === ($rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey))) {
                    continue;
                }

                $failures = [
                    ...$failures,
                    ...$this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey),
                ];
            }
        }

        return $failures;
    }

    private function buildMessage(string $key): string
    {
        return self::MESSAGES[$key] ?? throw InvalidEdifactContentException::messageUnknown($key);
    }
}
