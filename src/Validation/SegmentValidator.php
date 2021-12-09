<?php

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Elements;
use Apfelfrisch\Edifact\Exceptions\EdifactException;
use Iterator;
use Respect\Validation\Rules\Alpha;
use Respect\Validation\Rules\Digit;
use Respect\Validation\Rules\Length;
use Throwable;

class SegmentValidator
{
    public const ELEMENT_TYPE_ALPHA = 'a';
    public const ELEMENT_TYPE_NUMERIC = 'n';
    public const ELEMENT_TYPE_ALPHA_NUMERIC = 'an';
    public const ELEMENT_NEEDED = 'M';
    public const ELEMENT_DEPENDS = 'D';
    public const ELEMENT_OPTIONAL = 'O';

    /** @psalm-var array<string, string> */
    private array $messages = [
        Failure::VALUE_NOT_ALPHA => 'String must contain only alphabetic characters',
        Failure::VALUE_NOT_DIGIT => 'String must contain only digits',
        Failure::VALUE_TOO_LONG => 'String is more than %max% characters long',
        Failure::VALUE_TOO_SHORT => 'String is less than %min% characters long',
        Failure::VALUE_LENGTH_INVALID => 'String is not %len% characters long',
        Failure::UNKOWN_ELEMENT => 'The input Element is unkown',
        Failure::UNKOWN_COMPONENT => 'The input Component is unkown',
        Failure::MISSING_ELEMENT => 'The input Element is missing',
        Failure::MISSING_COMPONENT => 'Component with Id [%id%] is missing',
    ];

    private string $segname = '';
    private int $elementPosition = -1;
    private int $componentPosition = -1;
    private Failure|null $dependedFailure = null;

    /** @psalm-return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        $this->segname = $data->getName();
        $this->elementPosition = -1;

        $blueprintArray = $blueprint->toArray();
        $blueprintElementKeys = array_keys($blueprintArray);
        foreach ($data->toArray() as $elementKey => $components) {
            $this->elementPosition++;
            $this->componentPosition = -1;
            foreach ($components as $componentKey => $value) {
                $this->componentPosition++;

                foreach ($this->validateUnkownParts($blueprintArray, $elementKey, $componentKey, $value) as $failure) {
                    yield $failure;
                }

                /** @var string $rules */
                if (null === $rules = $blueprintArray[$elementKey][$componentKey] ?? null) {
                    continue;
                }

                foreach ($this->validateValues($value, $rules, $elementKey, $componentKey) as $failure) {
                    yield $failure;
                }
            }

            foreach ($this->validateMissingComponent($blueprintArray, $blueprintElementKeys) as $failure) {
                yield $failure;
            }
        }

        foreach($this->validateMissingElements($blueprintArray, $blueprintElementKeys) as $failure) {
            yield $failure;
        }
    }

    /** @psalm-return Iterator<Failure> */
    private function validateValues(string|null $value, string $rules, string $elementKey, string $componentKey): Iterator
    {
        [$necessaryState, $type, $minLenght, $maxLength] = $this->explodeRules($rules);

        if ($value === null || $value === '') {
            if ($necessaryState === self::ELEMENT_OPTIONAL) {
                return;
            }

            $failure = new Failure(
                Failure::MISSING_COMPONENT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMissingComponentMessage("$elementKey:$componentKey")
            );

            if ($necessaryState === self::ELEMENT_NEEDED) {
                yield $failure;
                return;
            }

            // If Component the component is only needed if the Element is set,
            // Save Failure for next iteration and check there if the Element is truly needed
            if ($necessaryState === self::ELEMENT_DEPENDS) {
                $this->dependedFailure = $failure;
                return;
            }
        }

        // Check if previosly needed Segement, was really needed
        if ($this->dependedFailure !== null) {
            $depenedFailure = $this->dependedFailure;
            $this->dependedFailure = null;
            if ($depenedFailure->getElementPosition() === $this->elementPosition) {
                yield $depenedFailure;
            }
        }

        if ($type === self::ELEMENT_TYPE_NUMERIC && ! (new Digit)->validate($value)) {
            yield new Failure(
                Failure::VALUE_NOT_DIGIT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::VALUE_NOT_DIGIT)
            );
        }

        if ($type === self::ELEMENT_TYPE_ALPHA && ! (new Alpha)->validate((string)$value)) {
            yield new Failure(
                Failure::VALUE_NOT_ALPHA ,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::VALUE_NOT_ALPHA)
            );
        }

        if ($minLenght !== $maxLength && ! (new Length($minLenght))->validate((string)$value)) {
            yield new Failure(
                Failure::VALUE_TOO_SHORT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildStringTooShortMessage($minLenght)
            );
            return;
        }

        if (! (new Length($minLenght, $maxLength))->validate((string)$value)) {
            if ($minLenght === $maxLength) {
                yield new Failure(
                    Failure::VALUE_LENGTH_INVALID,
                    $this->segname,
                    $this->elementPosition,
                    $this->componentPosition,
                    $value,
                    $this->buildInvalidStringLengtMessage($maxLength)
                );
                return;
            }

            yield new Failure(
                Failure::VALUE_TOO_LONG,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildStringTooLongMessage($maxLength)
            );
        }
    }

    /**
     * @psalm-param array<string, array<string, string|null>> $blueprintArray
     *
     * @psalm-return Iterator<Failure>
     */
    private function validateUnkownParts(array $blueprintArray, string $elementKey, string $componentKey, string|null $value): Iterator
    {
        if (! array_key_exists($elementKey, $blueprintArray)) {
            yield new Failure(
                Failure::UNKOWN_ELEMENT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                $value,
                $this->buildMessage(Failure::UNKOWN_ELEMENT)
            );
        } elseif (! array_key_exists($componentKey, $blueprintArray[$elementKey])) {
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
     * @psalm-param array<string, array<string, string|null>> $blueprintArray
     * @psalm-param list<string> $blueprintElementKeys
     *
     * @psalm-return Iterator<Failure>
     */
    private function validateMissingComponent(array $blueprintArray, array $blueprintElementKeys): Iterator
    {
        if (null === $blueprintElementKey = $blueprintElementKeys[$this->elementPosition] ?? null) {
            return;
        }

        $blueprintComponentKeys = array_keys($blueprintArray[$blueprintElementKey]);
        $blueprintComponentPosition = $this->componentPosition + 1;
        while($blueprintComponentKey = $blueprintComponentKeys[$blueprintComponentPosition] ?? null) {
            $blueprintComponentPosition++;

            /** @var string $rules */
            if (null === $rules = $blueprintArray[$blueprintElementKey][$blueprintComponentKey]) {
                continue;
            }

            [$necessaryState] = $this->explodeRules($rules);

            if ($necessaryState === self::ELEMENT_OPTIONAL) {
                continue;
            }

            yield new Failure(
                Failure::MISSING_COMPONENT,
                $this->segname,
                $this->elementPosition,
                $this->componentPosition,
                null,
                $this->buildMissingComponentMessage("$blueprintElementKey:$blueprintComponentKey")
            );
        }
    }

    /**
     * @psalm-param array<string, array<string, string|null>> $blueprintArray
     * @psalm-param list<string> $blueprintElementKeys
     *
     * @psalm-return Iterator<Failure>
     **/
    private function validateMissingElements(array $blueprintArray, array $blueprintElementKeys): Iterator
    {
        $blueprintElementPosition = $this->elementPosition + 1;

        while ($blueprintElementKey = $blueprintElementKeys[$blueprintElementPosition] ?? null) {
            $blueprintElementPosition++;
            $blueprintComponentKeys = array_keys($blueprintArray[$blueprintElementKey]);

            foreach ($blueprintComponentKeys as $blueprintComponentKey) {

                /** @var string $rules */
                if (null === $rules = $blueprintArray[$blueprintElementKey][$blueprintComponentKey]) {
                    continue;
                }

                [$necessaryState] = $this->explodeRules($rules);

                if ($necessaryState !== self::ELEMENT_NEEDED) {
                    continue;
                }

                yield new Failure(
                    Failure::MISSING_ELEMENT,
                    $this->segname,
                    $this->elementPosition,
                    0,
                    null,
                    $this->buildMissingComponentMessage("$blueprintElementKey:$blueprintComponentKey")
                );
            }
        }
    }

    private function buildMissingComponentMessage(string $id): string
    {
        return str_replace('%id%', $id, $this->buildMessage(Failure::MISSING_COMPONENT));
    }

    private function buildStringTooShortMessage(int $stringLength): string
    {
        return str_replace('%min%', (string) $stringLength, $this->buildMessage(Failure::VALUE_TOO_SHORT));
    }

    private function buildStringTooLongMessage(int $stringLength): string
    {
        return str_replace('%max%', (string) $stringLength, $this->buildMessage(Failure::VALUE_TOO_LONG));
    }

    private function buildInvalidStringLengtMessage(int $stringLength): string
    {
        return str_replace('%len%', (string) $stringLength, $this->buildMessage(Failure::VALUE_LENGTH_INVALID));
    }

    private function buildMessage(string $key): string
    {
        return $this->messages[$key] ?? throw new EdifactException("Unkown message key [$key]");
    }

    /**
     * @psalm-return array{string, string, int, int}
     */
    private function explodeRules(string $rules): array
    {
        try {
            $rulesArray = explode('|', $rules);

            if (substr($rulesArray[2], 0, 2) === '..') {
                return [
                    $rulesArray[0],
                    $rulesArray[1],
                    1,
                    (int)substr($rulesArray[2], 2)
                ];
            }

            return [
                $rulesArray[0],
                $rulesArray[1],
                (int)$rulesArray[2],
                (int)$rulesArray[2]
            ];
        } catch (Throwable) {
            throw new EdifactException("Invalid Validation Rule [$rules]");
        }
    }
}
