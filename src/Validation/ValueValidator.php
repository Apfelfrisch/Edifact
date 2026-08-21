<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;
use Apfelfrisch\Edifact\Validation\Rules\HasStringLength;
use Apfelfrisch\Edifact\Validation\Rules\IsAlpha;
use Apfelfrisch\Edifact\Validation\Rules\IsNumber;

final class ValueValidator
{
    public const string ELEMENT_TYPE_ALPHA = 'a';
    public const string ELEMENT_TYPE_NUMERIC = 'n';
    public const string ELEMENT_TYPE_ALPHA_NUMERIC = 'an';
    public const string ELEMENT_NEEDED = 'M';
    public const string ELEMENT_DEPENDS = 'D';
    public const string ELEMENT_OPTIONAL = 'O';

    private const array MESSAGES = [
        Failure::VALUE_NOT_ALPHA => 'String must contain only alphabetic characters',
        Failure::VALUE_NOT_DIGIT => 'String must contain only digits',
        Failure::VALUE_TOO_LONG => 'String is more than % characters long',
        Failure::VALUE_TOO_SHORT => 'String is less than % characters long',
        Failure::VALUE_LENGTH_INVALID => 'String is not % characters long',
        Failure::MISSING_COMPONENT => 'Component with Id [%] is missing',
    ];

    private ?string $missingElementPosition = null;

    /**
     * @return array<string, string>
     */
    public function validate(?string $value, string $rules, string $elementPosition): array
    {
        [$necessaryState, $type, $minLength, $maxLength] = $this->explodeRules($rules);

        $knownStates = [self::ELEMENT_OPTIONAL, self::ELEMENT_NEEDED, self::ELEMENT_DEPENDS];

        if (($value === null || $value === '') && in_array($necessaryState, $knownStates, true)) {
            return $this->validateMissingValue($necessaryState, $elementPosition);
        }

        // Check if the previously saved dependent component was really needed
        if ($this->missingElementPosition !== null) {
            if ($this->missingElementPosition === $elementPosition) {
                return [Failure::MISSING_COMPONENT => $this->buildMessage(Failure::MISSING_COMPONENT)];
            }

            $this->missingElementPosition = null;
        }

        $value ??= '';

        return [...$this->validateType($value, $type), ...$this->validateLength($value, $minLength, $maxLength)];
    }

    /**
     * @return array<string, string>
     */
    private function validateMissingValue(string $necessaryState, string $elementPosition): array
    {
        if ($necessaryState === self::ELEMENT_NEEDED) {
            return [Failure::MISSING_COMPONENT => $this->buildMessage(Failure::MISSING_COMPONENT)];
        }

        // A dependent component is only needed if its element is set at all.
        // Save the failure for the next iteration and check there if the element is truly needed.
        if ($necessaryState === self::ELEMENT_DEPENDS) {
            $this->missingElementPosition = $elementPosition;
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private function validateType(string $value, string $type): array
    {
        if ($type === self::ELEMENT_TYPE_NUMERIC && !(new IsNumber())($value)) {
            return [Failure::VALUE_NOT_DIGIT => $this->buildMessage(Failure::VALUE_NOT_DIGIT)];
        }

        if ($type === self::ELEMENT_TYPE_ALPHA && !(new IsAlpha())($value)) {
            return [Failure::VALUE_NOT_ALPHA => $this->buildMessage(Failure::VALUE_NOT_ALPHA)];
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    private function validateLength(string $value, int $minLength, int $maxLength): array
    {
        if ($minLength === $maxLength) {
            if (new HasStringLength()->min($minLength)->max($maxLength)($value)) {
                return [];
            }

            return [
                Failure::VALUE_LENGTH_INVALID => $this->buildMessage(
                    Failure::VALUE_LENGTH_INVALID,
                    (string) $minLength,
                ),
            ];
        }

        if (!new HasStringLength()->min($minLength)($value)) {
            return [Failure::VALUE_TOO_SHORT => $this->buildMessage(Failure::VALUE_TOO_SHORT, (string) $minLength)];
        }

        if (!new HasStringLength()->max($maxLength)($value)) {
            return [Failure::VALUE_TOO_LONG => $this->buildMessage(Failure::VALUE_TOO_LONG, (string) $maxLength)];
        }

        return [];
    }

    /**
     * @return array{string, string, int, int}
     */
    private function explodeRules(string $rules): array
    {
        $parts = explode('|', $rules);

        if (count($parts) < 3) {
            throw ValidationException::invalidRule($rules);
        }

        [$necessaryState, $type, $length] = $parts;

        if (str_starts_with($length, '..')) {
            return [$necessaryState, $type, 1, (int) substr($length, 2)];
        }

        return [$necessaryState, $type, (int) $length, (int) $length];
    }

    private function buildMessage(string $key, ?string $replacement = null): string
    {
        $message = self::MESSAGES[$key] ?? throw InvalidEdifactContentException::messageUnknown($key);

        if ($replacement !== null) {
            return str_replace('%', $replacement, $message);
        }

        return $message;
    }
}
