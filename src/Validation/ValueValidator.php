<?php

declare(strict_types=1);

namespace Apfelfrisch\Edifact\Validation;

use Apfelfrisch\Edifact\Exceptions\InvalidEdifactContentException;
use Apfelfrisch\Edifact\Exceptions\ValidationException;

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

    private const array KNOWN_STATES = [self::ELEMENT_OPTIONAL, self::ELEMENT_NEEDED, self::ELEMENT_DEPENDS];

    /** @var array<string, array{string, string, int, int}> */
    private static array $explodedRulesCache = [];

    private null|string $missingElementPosition = null;

    /**
     * @return array<string, string>
     */
    public function validate(null|string $value, string $rules, string $elementPosition): array
    {
        $explodedRules = self::$explodedRulesCache[$rules] ?? null;

        if ($explodedRules === null) {
            $explodedRules = $this->explodeRules($rules);
            self::$explodedRulesCache[$rules] = $explodedRules;
        }

        [$necessaryState, $type, $minLength, $maxLength] = $explodedRules;

        if (($value === null || $value === '') && in_array($necessaryState, self::KNOWN_STATES, true)) {
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

        $failures = [];

        // Inlined IsNumber rule
        if ($type === self::ELEMENT_TYPE_NUMERIC && (!is_numeric($value) || is_nan((float) $value))) {
            $failures[Failure::VALUE_NOT_DIGIT] = $this->buildMessage(Failure::VALUE_NOT_DIGIT);
        }

        // Inlined IsAlpha rule
        if ($type === self::ELEMENT_TYPE_ALPHA && !ctype_alpha($value)) {
            $failures[Failure::VALUE_NOT_ALPHA] = $this->buildMessage(Failure::VALUE_NOT_ALPHA);
        }

        $length = mb_strlen($value);

        if ($minLength === $maxLength && $length !== $minLength) {
            $failures[Failure::VALUE_LENGTH_INVALID] = $this->buildMessage(
                Failure::VALUE_LENGTH_INVALID,
                (string) $minLength,
            );
        }

        if ($minLength !== $maxLength && $length < $minLength) {
            $failures[Failure::VALUE_TOO_SHORT] = $this->buildMessage(Failure::VALUE_TOO_SHORT, (string) $minLength);
        }

        if ($minLength !== $maxLength && $length > $maxLength) {
            $failures[Failure::VALUE_TOO_LONG] = $this->buildMessage(Failure::VALUE_TOO_LONG, (string) $maxLength);
        }

        return $failures;
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

    private function buildMessage(string $key, null|string $replacement = null): string
    {
        $message = self::MESSAGES[$key] ?? throw InvalidEdifactContentException::messageUnknown($key);

        if ($replacement !== null) {
            return str_replace('%', $replacement, $message);
        }

        return $message;
    }
}
