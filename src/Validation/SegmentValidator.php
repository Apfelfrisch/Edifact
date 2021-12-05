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
    ];

    /** @psalm-return Iterator<Failure> */
    public function validate(Elements $blueprint, Elements $data): Iterator
    {
        $blueprintArray = $blueprint->toArray();

        $elementPosition = -1;
        foreach ($data->toArray() as $elementKey => $components) {
            $elementPosition++;
            $componentPosition = -1;
            foreach ($components as $componentKey => $value) {
                $componentPosition++;

                if (! array_key_exists($elementKey, $blueprintArray)) {
                    yield new Failure(
                        Failure::UNKOWN_ELEMENT,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildMessage(Failure::UNKOWN_ELEMENT)
                    );
                    continue;
                }

                if (! array_key_exists($componentKey, $blueprintArray[$elementKey])) {
                    yield new Failure(
                        Failure::UNKOWN_COMPONENT,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildMessage(Failure::UNKOWN_COMPONENT)
                    );
                    continue;
                }

                /** @var string $rules */
                $rules = $blueprintArray[$elementKey][$componentKey];

                [$necessaryState, $type, $minLenght, $maxLength] = $this->explodeRules($rules);

                if ($necessaryState === self::ELEMENT_OPTIONAL && $value === null) {
                    continue;
                }

                if ($type === self::ELEMENT_TYPE_NUMERIC && ! (new Digit)->validate($value)) {
                    yield new Failure(
                        Failure::VALUE_NOT_DIGIT,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildMessage(Failure::VALUE_NOT_DIGIT)
                    );
                }

                if ($type === self::ELEMENT_TYPE_ALPHA && ! (new Alpha)->validate((string)$value)) {
                    yield new Failure(
                        Failure::VALUE_NOT_ALPHA ,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildMessage(Failure::VALUE_NOT_ALPHA)
                    );
                }

                if ($minLenght !== $maxLength && ! (new Length($minLenght))->validate((string)$value)) {
                    yield new Failure(
                        Failure::VALUE_TOO_SHORT,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildStringTooShortMessage($minLenght)
                    );
                    continue;
                }

                if (! (new Length($minLenght, $maxLength))->validate((string)$value)) {
                    if ($minLenght === $maxLength) {
                        yield new Failure(
                            Failure::VALUE_LENGTH_INVALID,
                            $data->getName(),
                            $elementPosition,
                            $componentPosition,
                            $value,
                            $this->buildInvalidStringLengtMessage($maxLength)
                        );
                        continue;
                    }
                    yield new Failure(
                        Failure::VALUE_TOO_LONG,
                        $data->getName(),
                        $elementPosition,
                        $componentPosition,
                        $value,
                        $this->buildStringTooLongMessage($maxLength)
                    );
                }
            }
        }
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
}
