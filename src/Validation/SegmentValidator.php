<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Elements;
use Iterator;

final class SegmentValidator
{
    private const array MESSAGES = [
        Failure::UNKNOWN_ELEMENT => 'The input Element is unknown',
        Failure::UNKNOWN_COMPONENT => 'The input Component is unknown',
    ];

    private readonly ValueValidator $valueValidator;

    private string $segmentName = '';

    private int $elementPosition = -1;

    private int $componentPosition = -1;

    public function __construct()
    {
        $this->valueValidator = new ValueValidator();
    }

    /** @return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        $this->segmentName = $data->getName();
        $this->elementPosition = -1;

        $blueprintElementKeys = $blueprint->getElementKeys();

        foreach ($data->toArray() as $elementKey => $components) {
            $elementKey = (string) $elementKey;
            $this->elementPosition++;
            $this->componentPosition = -1;

            foreach ($components as $componentKey => $value) {
                $componentKey = (string) $componentKey;
                $this->componentPosition++;

                yield from $this->validateUnknownParts($blueprint, $elementKey, $componentKey, $value);

                if (null !== ($rules = $blueprint->getValue($elementKey, $componentKey))) {
                    yield from $this->validateValue($value, $rules, $elementKey, $componentKey);
                }
            }

            yield from $this->validateMissingComponents($blueprint, $blueprintElementKeys);
        }

        yield from $this->validateMissingElements($blueprint, $blueprintElementKeys);
    }

    /**
     * @return Iterator<Failure>
     */
    private function validateValue(
        null|string $value,
        string $rules,
        string $elementKey,
        string $componentKey,
    ): Iterator {
        $failures = $this->valueValidator->validate($value, $rules, (string) $this->elementPosition);

        foreach ($failures as $failureType => $message) {
            $message = match ($failureType) {
                Failure::MISSING_COMPONENT => str_replace('%', "{$elementKey}:{$componentKey}", $message),
                default => $message,
            };

            yield new Failure(
                $failureType,
                $this->segmentName,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $message,
            );
        }
    }

    /**
     * @return Iterator<Failure>
     */
    private function validateUnknownParts(
        Elements $blueprint,
        string $elementKey,
        string $componentKey,
        null|string $value,
    ): Iterator {
        if (($element = $blueprint->getElement($elementKey)) === []) {
            yield new Failure(
                Failure::UNKNOWN_ELEMENT,
                $this->segmentName,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::UNKNOWN_ELEMENT),
            );

            return;
        }

        if (!array_key_exists($componentKey, $element)) {
            yield new Failure(
                Failure::UNKNOWN_COMPONENT,
                $this->segmentName,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::UNKNOWN_COMPONENT),
            );
        }
    }

    /**
     * @param list<string> $blueprintElementKeys
     *
     * @return Iterator<Failure>
     */
    private function validateMissingComponents(Elements $blueprint, array $blueprintElementKeys): Iterator
    {
        if (null === ($blueprintElementKey = $blueprintElementKeys[$this->elementPosition] ?? null)) {
            return;
        }

        $blueprintComponentKeys = $blueprint->getComponentKeys($blueprintElementKey);
        $blueprintComponentPosition = $this->componentPosition + 1;

        while (null !== ($blueprintComponentKey = $blueprintComponentKeys[$blueprintComponentPosition] ?? null)) {
            $blueprintComponentPosition++;

            if (null === ($rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey))) {
                continue;
            }

            yield from $this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey);
        }
    }

    /**
     * @param list<string> $blueprintElementKeys
     *
     * @return Iterator<Failure>
     */
    private function validateMissingElements(Elements $blueprint, array $blueprintElementKeys): Iterator
    {
        $blueprintElementPosition = $this->elementPosition + 1;

        while (null !== ($blueprintElementKey = $blueprintElementKeys[$blueprintElementPosition] ?? null)) {
            $blueprintElementPosition++;

            foreach ($blueprint->getComponentKeys($blueprintElementKey) as $blueprintComponentKey) {
                if (null === ($rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey))) {
                    continue;
                }

                yield from $this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey);
            }
        }
    }

    private function buildMessage(string $key): string
    {
        return self::MESSAGES[$key] ?? throw InvalidEdifactContentException::messageUnknown($key);
    }
}
