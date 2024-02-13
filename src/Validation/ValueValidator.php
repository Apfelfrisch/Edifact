<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Validation\Rules\HasStringLength;
use Apfelfrisch\Edifact\Validation\Rules\IsAlpha;
use Apfelfrisch\Edifact\Validation\Rules\IsNumber;
use Throwable;

final class ValueValidator
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
        Failure::VALUE_TOO_LONG => 'String is more than % characters long',
        Failure::VALUE_TOO_SHORT => 'String is less than % characters long',
        Failure::VALUE_LENGTH_INVALID => 'String is not % characters long',
        Failure::MISSING_COMPONENT => 'Component with Id [%] is missing',
    ];

    private string|null $missingElementPosition = null;

    /**
     * @psalm-return array<string, string>
     */
    public function validate(string|null $value, string $rules, string $elementPosition): array
    {
        [$necessaryState, $type, $minLenght, $maxLength] = $this->explodeRules($rules);

        if ($value === null || $value === '') {
            if ($necessaryState === self::ELEMENT_OPTIONAL) {
                return [];
            }

            if ($necessaryState === self::ELEMENT_NEEDED) {
                return [Failure::MISSING_COMPONENT => $this->buildMessage(Failure::MISSING_COMPONENT)];
            }

            // If Component the component is only needed if the Element is set,
            // Save Failure for next iteration and check there if the Element is truly needed
            if ($necessaryState === self::ELEMENT_DEPENDS) {
                $this->missingElementPosition = $elementPosition;

                return [];
            }
        }

        // Check if previosly needed Segement, was really needed
        if ($this->missingElementPosition !== null) {
            if ($this->missingElementPosition === $elementPosition) {
                return [Failure::MISSING_COMPONENT => $this->buildMessage(Failure::MISSING_COMPONENT)];
            } else {
                $this->missingElementPosition = null;
            }
        }

        $failures = [];

        /** @var string $value : $value is at this position always a string */

        if ($type === self::ELEMENT_TYPE_NUMERIC && ! (new IsNumber())($value)) {
            $failures[Failure::VALUE_NOT_DIGIT] = $this->buildMessage(Failure::VALUE_NOT_DIGIT);
        }

        if ($type === self::ELEMENT_TYPE_ALPHA && ! (new IsAlpha())($value)) {
            $failures[Failure::VALUE_NOT_ALPHA] = $this->buildMessage(Failure::VALUE_NOT_ALPHA);
        }

        if ($minLenght !== $maxLength && ! (new HasStringLength())->min($minLenght)($value)) {
            $failures[Failure::VALUE_TOO_SHORT] = $this->buildMessage(Failure::VALUE_TOO_SHORT, (string)$minLenght);

            return $failures;
        }

        if (! (new HasStringLength())->min($minLenght)->max($maxLength)($value)) {
            if ($minLenght === $maxLength) {
                $failures[Failure::VALUE_LENGTH_INVALID] = $this->buildMessage(Failure::VALUE_LENGTH_INVALID, (string)$minLenght);

                return $failures;
            }

            $failures[Failure::VALUE_TOO_LONG] = $this->buildMessage(Failure::VALUE_TOO_LONG, (string)$maxLength);
        }

        return $failures;
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
                    (int)substr($rulesArray[2], 2),
                ];
            }

            return [
                $rulesArray[0],
                $rulesArray[1],
                (int)$rulesArray[2],
                (int)$rulesArray[2],
            ];
        } catch (Throwable) {
            throw ValidationException::invalidRule("Invalid Validation Rule [$rules]");
        }
    }

    private function buildMessage(string $key, string $replacement = null): string
    {
        $message = $this->messages[$key] ?? throw InvalidEdifactContentException::messageUnknown("Unkown message key [$key]");

        if ($replacement !== null) {
            $message = str_replace('%', $replacement, $message);
        }

        return $message;
    }
}
