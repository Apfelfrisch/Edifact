<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Segment\Elements;
use Iterator;

class SegmentValidator
{
    /** @psalm-var array<string, string> */
    private array $messages = [
        Failure::UNKOWN_ELEMENT => 'The input Element is unkown',
        Failure::UNKOWN_COMPONENT => 'The input Component is unkown',
    ];

    private ValueValidator $valueValidator;

    private string $segname = '';

    private int $elementPosition = -1;

    private int $componentPosition = -1;

    public function __construct()
    {
        $this->valueValidator = new ValueValidator;
    }

    /** @psalm-return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        $this->segname = $data->getName();
        $this->elementPosition = -1;

        $blueprintElementKeys = $blueprint->getElementKeys();

        foreach ($data->toArray() as $elementKey => $components) {
            $this->elementPosition++;
            $this->componentPosition = -1;

            foreach ($components as $componentKey => $value) {
                $this->componentPosition++;

                foreach ($this->validateUnkownParts($blueprint, $elementKey, $componentKey, $value) as $failure) {
                    yield $failure;
                }

                if (null !== $rules = $blueprint->getValue($elementKey, $componentKey)) {
                    foreach ($this->validateValue($value, $rules, $elementKey, $componentKey) as $failure) {
                        yield $failure;
                    }
                }
            }

            foreach ($this->validateMissingComponents($blueprint, $blueprintElementKeys) as $failure) {
                yield $failure;
            }
        }

        foreach($this->validateMissingElements($blueprint, $blueprintElementKeys) as $failure) {
            yield $failure;
        }
    }

    /**
     * @psalm-return Iterator<Failure>
     */
    private function validateValue(string|null $value, string $rules, string $elementKey, string $componentKey): Iterator
    {
        $failures = $this->valueValidator->validate($value, $rules, (string)$this->elementPosition);

        foreach ($failures as $failureType => $message) {
            $message = match($failureType) {
                Failure::MISSING_COMPONENT => str_replace('%', "$elementKey:$componentKey", $message),
                Failure::VALUE_TOO_SHORT => str_replace('%', "$elementKey:$componentKey", $message),
                default => $message,
            };

            yield new Failure(
                $failureType,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $message
            );
        }
    }

    /**
     * @psalm-return Iterator<Failure>
     */
    private function validateUnkownParts(Elements $blueprint, string $elementKey, string $componentKey, string|null $value): Iterator
    {
        if (($element = $blueprint->getElement($elementKey)) === []) {
            yield new Failure(
                Failure::UNKOWN_ELEMENT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::UNKOWN_ELEMENT)
            );
        } elseif (! array_key_exists($componentKey, $element)) {
            yield new Failure(
                Failure::UNKOWN_COMPONENT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::UNKOWN_COMPONENT)
            );
        }
    }

    /**
     * @psalm-param list<string> $blueprintElementKeys
     *
     * @psalm-return Iterator<Failure>
     */
    private function validateMissingComponents(Elements $blueprint, array $blueprintElementKeys): Iterator
    {
        if (null === $blueprintElementKey = $blueprintElementKeys[$this->elementPosition] ?? null) {
            return;
        }

        $blueprintComponentKeys = $blueprint->getComponentKeys($blueprintElementKey);
        $blueprintComponentPosition = $this->componentPosition + 1;
        while($blueprintComponentKey = $blueprintComponentKeys[$blueprintComponentPosition] ?? null) {
            $blueprintComponentPosition++;
            /** @var string $rules */
            if (null === $rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey)) {
                continue;
            }

            foreach ($this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey) as $failure) {
                yield $failure;
            }
        }
    }

    /**
     * @psalm-param list<string> $blueprintElementKeys
     *
     * @psalm-return Iterator<Failure>
     **/
    private function validateMissingElements(Elements $blueprint, array $blueprintElementKeys): Iterator
    {
        $blueprintElementPosition = $this->elementPosition + 1;

        while ($blueprintElementKey = $blueprintElementKeys[$blueprintElementPosition] ?? null) {
            $blueprintElementPosition++;
            $blueprintComponentKeys = $blueprint->getComponentKeys($blueprintElementKey);

            foreach ($blueprintComponentKeys as $blueprintComponentKey) {

                /** @var string $rules */
                if (null === $rules = $blueprint->getValue($blueprintElementKey, $blueprintComponentKey)) {
                    continue;
                }

                foreach ($this->validateValue(null, $rules, $blueprintElementKey, $blueprintComponentKey) as $failure) {
                    yield $failure;
                }
            }
        }
    }

    private function buildMessage(string $key): string
    {
        return $this->messages[$key] ?? throw InvalidEdifactContentException::messageUnknown($key);
    }
}
